<?php

namespace App\Support\Backups;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class DatabaseBackupManager
{
    private const FILENAME_PATTERN = '/\Akoni-db-\d{8}-\d{6}-[a-z0-9]{8}\.sql\z/';

    public function create(): array
    {
        $connection = $this->connectionConfig();
        $database = $this->databaseName($connection);
        $directory = $this->ensureDirectory();
        $filename = 'koni-db-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(8)).'.sql';
        $path = $directory.DIRECTORY_SEPARATOR.$filename;
        $credentialsFile = null;

        try {
            $credentialsFile = $this->createCredentialsFile($connection);

            $process = new Process([
                (string) config('koni-backups.mysqldump_binary', 'mysqldump'),
                '--defaults-extra-file='.$credentialsFile,
                '--single-transaction',
                '--quick',
                '--routines',
                '--triggers',
                '--events',
                '--hex-blob',
                '--default-character-set=utf8mb4',
                '--set-gtid-purged=OFF',
                '--no-tablespaces',
                '--add-drop-table',
                '--skip-lock-tables',
                '--result-file='.$path,
                $database,
            ]);
            $process->setTimeout($this->processTimeout());
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException('Proses mysqldump gagal. Periksa binary dan hak akses database.');
            }

            clearstatcache(true, $path);

            if (! is_file($path) || filesize($path) < 16) {
                throw new RuntimeException('File backup tidak terbentuk atau ukurannya tidak valid.');
            }

            if (! chmod($path, 0600)) {
                throw new RuntimeException('Permission file backup tidak dapat diamankan.');
            }

            $manifest = [
                'format_version' => 1,
                'application' => 'KONI Klaten',
                'driver' => 'mysql',
                'database' => $database,
                'file' => $filename,
                'created_at' => now()->toIso8601String(),
                'size_bytes' => filesize($path),
                'sha256' => hash_file('sha256', $path),
            ];

            $this->writeManifest($path.'.json', $manifest);

            $this->recordSystemEvent('backup_created', $filename, [
                'database' => $database,
                'size_bytes' => $manifest['size_bytes'],
                'sha256' => $manifest['sha256'],
            ]);

            return $manifest;
        } catch (Throwable $exception) {
            if (is_file($path)) {
                @unlink($path);
            }

            if (is_file($path.'.json')) {
                @unlink($path.'.json');
            }

            $this->recordSystemEvent('backup_failed', $filename, [
                'error_type' => class_basename($exception),
            ]);

            throw $exception;
        } finally {
            if ($credentialsFile && is_file($credentialsFile)) {
                @unlink($credentialsFile);
            }
        }
    }

    public function verify(string $filename): array
    {
        $path = $this->resolveBackupPath($filename);
        $manifestPath = $path.'.json';
        clearstatcache(true, $path);
        clearstatcache(true, $manifestPath);

        if (is_link($path) || ! is_file($path)) {
            throw new RuntimeException('File backup tidak ditemukan atau tidak valid.');
        }

        if (is_link($manifestPath) || ! is_file($manifestPath)) {
            throw new RuntimeException('Manifest backup tidak ditemukan.');
        }

        try {
            $manifest = json_decode(
                (string) file_get_contents($manifestPath),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Throwable) {
            throw new RuntimeException('Manifest backup rusak atau tidak dapat dibaca.');
        }

        if (
            ! is_array($manifest)
            || ($manifest['format_version'] ?? null) !== 1
            || ($manifest['driver'] ?? null) !== 'mysql'
            || ($manifest['file'] ?? null) !== $filename
            || ($manifest['database'] ?? null) !== $this->databaseName($this->connectionConfig())
            || ! is_string($manifest['sha256'] ?? null)
            || ! preg_match('/\A[a-f0-9]{64}\z/i', $manifest['sha256'])
            || ! is_numeric($manifest['size_bytes'] ?? null)
            || (int) $manifest['size_bytes'] < 16
        ) {
            throw new RuntimeException('Metadata backup tidak sesuai dengan konfigurasi database.');
        }

        $actualSize = filesize($path);
        $actualHash = hash_file('sha256', $path);

        if (
            $actualSize === false
            || (int) $manifest['size_bytes'] !== $actualSize
            || ! is_string($actualHash)
            || ! hash_equals((string) $manifest['sha256'], $actualHash)
        ) {
            throw new RuntimeException('Integritas backup gagal. File mungkin berubah atau rusak.');
        }

        return [
            'path' => $path,
            'filename' => $filename,
            'manifest' => $manifest,
            'size_bytes' => $actualSize,
        ];
    }

    public function verifyAll(): array
    {
        $directory = $this->ensureDirectory();
        $results = [];

        foreach (glob($directory.DIRECTORY_SEPARATOR.'koni-db-*.sql') ?: [] as $path) {
            $filename = basename($path);

            try {
                $verified = $this->verify($filename);
                $results[] = [
                    'filename' => $filename,
                    'created_at' => $verified['manifest']['created_at'] ?? null,
                    'size_bytes' => $verified['size_bytes'],
                    'valid' => true,
                    'message' => 'OK',
                ];
            } catch (Throwable $exception) {
                $results[] = [
                    'filename' => $filename,
                    'created_at' => null,
                    'size_bytes' => is_file($path) ? (filesize($path) ?: 0) : 0,
                    'valid' => false,
                    'message' => $exception->getMessage(),
                ];
            }
        }

        usort(
            $results,
            fn (array $left, array $right): int => strcmp($right['filename'], $left['filename'])
        );

        return $results;
    }

    public function prune(int $retentionDays): int
    {
        if ($retentionDays < 1 || $retentionDays > 3650) {
            throw new RuntimeException('Retensi backup harus antara 1 sampai 3650 hari.');
        }

        $directory = $this->ensureDirectory();
        $cutoff = now()->subDays($retentionDays)->getTimestamp();
        $removed = 0;

        foreach (glob($directory.DIRECTORY_SEPARATOR.'koni-db-*.sql') ?: [] as $path) {
            $filename = basename($path);

            if (! preg_match(self::FILENAME_PATTERN, $filename) || is_link($path) || ! is_file($path)) {
                continue;
            }

            $modifiedAt = filemtime($path);

            if ($modifiedAt === false || $modifiedAt >= $cutoff) {
                continue;
            }

            $manifestPath = $path.'.json';

            if (@unlink($path)) {
                if (is_file($manifestPath) && ! is_link($manifestPath)) {
                    @unlink($manifestPath);
                }

                $removed++;
            }
        }

        if ($removed > 0) {
            $this->recordSystemEvent('backup_pruned', 'Backup kedaluwarsa', [
                'removed_files' => $removed,
                'retention_days' => $retentionDays,
            ]);
        }

        return $removed;
    }

    public function restore(string $filename): array
    {
        $verified = $this->verify($filename);
        $connection = $this->connectionConfig();
        $database = $this->databaseName($connection);
        $credentialsFile = $this->createCredentialsFile($connection);
        $input = null;

        try {
            $input = fopen($verified['path'], 'rb');

            if ($input === false) {
                throw new RuntimeException('File backup tidak dapat dibuka.');
            }

            $process = new Process([
                (string) config('koni-backups.mysql_binary', 'mysql'),
                '--defaults-extra-file='.$credentialsFile,
                '--default-character-set=utf8mb4',
                '--binary-mode',
                '--database='.$database,
            ]);
            $process->setInput($input);
            $process->setTimeout($this->processTimeout());
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException('Proses pemulihan database gagal. Periksa hak akses dan log MySQL.');
            }

            $this->recordSystemEvent('backup_restored', $filename, [
                'database' => $database,
                'sha256' => $verified['manifest']['sha256'],
            ]);

            return $verified['manifest'];
        } finally {
            if (is_resource($input)) {
                fclose($input);
            }

            if (is_file($credentialsFile)) {
                @unlink($credentialsFile);
            }
        }
    }

    public function backupDirectory(): string
    {
        return $this->ensureDirectory();
    }

    private function connectionConfig(): array
    {
        $connectionName = config('koni-backups.connection') ?: config('database.default');
        $connection = config('database.connections.'.$connectionName);

        if (! is_array($connection) || ($connection['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('Backup Patch 19 saat ini mendukung koneksi database MySQL.');
        }

        return $connection;
    }

    private function databaseName(array $connection): string
    {
        $database = (string) ($connection['database'] ?? '');

        if ($database === '' || ! preg_match('/\A[a-zA-Z0-9_][a-zA-Z0-9_$-]*\z/', $database)) {
            throw new RuntimeException('Nama database MySQL tidak valid untuk proses backup.');
        }

        return $database;
    }

    private function ensureDirectory(): string
    {
        $directory = (string) config(
            'koni-backups.directory',
            storage_path('app/private/backups')
        );

        $publicRoot = realpath(public_path());
        $candidate = rtrim($directory, DIRECTORY_SEPARATOR);

        if ($candidate === '') {
            throw new RuntimeException('Lokasi backup tidak valid.');
        }

        if (! is_dir($candidate) && ! mkdir($candidate, 0700, true) && ! is_dir($candidate)) {
            throw new RuntimeException('Direktori backup tidak dapat dibuat.');
        }

        $realDirectory = realpath($candidate);

        if (
            $realDirectory === false
            || ($publicRoot && (
                $realDirectory === $publicRoot
                || str_starts_with($realDirectory.DIRECTORY_SEPARATOR, $publicRoot.DIRECTORY_SEPARATOR)
            ))
        ) {
            throw new RuntimeException('Lokasi backup harus berada di luar direktori publik.');
        }

        if (! chmod($realDirectory, 0700)) {
            throw new RuntimeException('Permission direktori backup tidak dapat diamankan.');
        }

        return $realDirectory;
    }

    private function createCredentialsFile(array $connection): string
    {
        $cacheDirectory = storage_path('framework/cache');

        if (! is_dir($cacheDirectory) && ! mkdir($cacheDirectory, 0700, true) && ! is_dir($cacheDirectory)) {
            throw new RuntimeException('Direktori sementara tidak dapat dibuat.');
        }

        $path = tempnam($cacheDirectory, 'koni-db-client-');

        if ($path === false) {
            throw new RuntimeException('File kredensial sementara tidak dapat dibuat.');
        }

        $host = $connection['host'] ?? '127.0.0.1';
        if (is_array($host)) {
            $host = reset($host) ?: '127.0.0.1';
        }

        $lines = ['[client]'];

        if (! empty($connection['unix_socket'])) {
            $lines[] = 'socket="'.$this->escapeOption((string) $connection['unix_socket']).'"';
        } else {
            $lines[] = 'host="'.$this->escapeOption((string) $host).'"';
            $lines[] = 'port="'.(int) ($connection['port'] ?? 3306).'"';
            $lines[] = 'protocol=tcp';
        }

        $lines[] = 'user="'.$this->escapeOption((string) ($connection['username'] ?? '')).'"';
        $lines[] = 'password="'.$this->escapeOption((string) ($connection['password'] ?? '')).'"';

        if (file_put_contents($path, implode("\n", $lines)."\n", LOCK_EX) === false) {
            @unlink($path);
            throw new RuntimeException('File kredensial sementara tidak dapat ditulis.');
        }

        if (! chmod($path, 0600)) {
            @unlink($path);
            throw new RuntimeException('Permission file kredensial sementara tidak dapat diamankan.');
        }

        return $path;
    }

    private function escapeOption(string $value): string
    {
        return str_replace(
            ["\\", '"', "\n", "\r"],
            ["\\\\", '\\"', '\\n', '\\r'],
            $value
        );
    }

    private function writeManifest(string $path, array $manifest): void
    {
        $temporaryPath = $path.'.tmp-'.Str::lower(Str::random(8));
        $json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        try {
            if (file_put_contents($temporaryPath, $json."\n", LOCK_EX) === false) {
                throw new RuntimeException('Manifest backup tidak dapat ditulis.');
            }

            if (! chmod($temporaryPath, 0600)) {
                throw new RuntimeException('Permission manifest backup tidak dapat diamankan.');
            }

            if (! rename($temporaryPath, $path)) {
                throw new RuntimeException('Manifest backup tidak dapat dipasang.');
            }

            if (! chmod($path, 0600)) {
                throw new RuntimeException('Permission manifest backup tidak dapat diamankan.');
            }
        } finally {
            if (is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }
    }

    private function resolveBackupPath(string $filename): string
    {
        if (
            basename($filename) !== $filename
            || ! preg_match(self::FILENAME_PATTERN, $filename)
        ) {
            throw new RuntimeException('Nama file backup tidak valid.');
        }

        return $this->ensureDirectory().DIRECTORY_SEPARATOR.$filename;
    }

    private function processTimeout(): int
    {
        $timeout = (int) config('koni-backups.process_timeout', 1800);

        if ($timeout < 1 || $timeout > 86400) {
            throw new RuntimeException('Timeout proses backup harus antara 1 sampai 86400 detik.');
        }

        return $timeout;
    }

    private function recordSystemEvent(string $event, string $label, array $details = []): void
    {
        try {
            AuditLog::query()->create([
                'actor_id' => null,
                'actor_name' => 'System Backup',
                'actor_role' => 'system',
                'event' => $event,
                'subject_label' => Str::limit($label, 255, ''),
                'old_values' => null,
                'new_values' => $details ?: null,
                'ip_address' => null,
                'user_agent' => 'Artisan CLI',
                'method' => 'CLI',
                'route_name' => null,
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('KONI backup audit event could not be recorded.', [
                'event' => $event,
                'exception' => class_basename($exception),
            ]);
        }
    }
}
