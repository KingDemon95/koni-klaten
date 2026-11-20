<?php

namespace App\Support\Backups;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class RemoteBackupManager
{
    public function isEnabled(): bool
    {
        $disk = config('koni-backups.remote_disk');

        return is_string($disk) && trim($disk) !== '';
    }

    public function sync(?string $filename = null): array
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('Penyimpanan backup off-site belum dikonfigurasi.');
        }

        $backupManager = app(DatabaseBackupManager::class);
        $filename ??= $this->latestValidFilename($backupManager);
        $verified = $backupManager->verify($filename);
        $diskName = $this->diskName();
        $disk = Storage::disk($diskName);
        [$remoteSql, $remoteManifest] = $this->remotePaths($filename);

        try {
            $sqlStream = fopen($verified['path'], 'rb');

            if ($sqlStream === false) {
                throw new RuntimeException('File backup lokal tidak dapat dibuka untuk sinkronisasi.');
            }

            try {
                if (! $disk->put($remoteSql, $sqlStream, ['visibility' => 'private'])) {
                    throw new RuntimeException('Upload file SQL ke penyimpanan off-site gagal.');
                }
            } finally {
                fclose($sqlStream);
            }

            $manifestContents = file_get_contents($verified['path'].'.json');

            if (! is_string($manifestContents) || ! $disk->put($remoteManifest, $manifestContents, ['visibility' => 'private'])) {
                throw new RuntimeException('Upload manifest ke penyimpanan off-site gagal.');
            }

            $status = $this->status($filename);

            if (($status['status'] ?? null) !== 'ok') {
                throw new RuntimeException('Verifikasi salinan off-site gagal.');
            }
        } catch (Throwable $exception) {
            try {
                $disk->delete([$remoteSql, $remoteManifest]);
            } catch (Throwable) {
                // Keep the original upload failure as the actionable error.
            }

            $this->recordSystemEvent('backup_remote_failed', $filename, [
                'disk' => $diskName,
                'error_type' => class_basename($exception),
            ]);

            throw $exception;
        }

        $this->recordSystemEvent('backup_remote_synced', $filename, [
            'disk' => $diskName,
            'size_bytes' => $verified['size_bytes'],
            'sha256' => $verified['manifest']['sha256'],
        ]);

        return [
            'filename' => $filename,
            'disk' => $diskName,
            'remote_path' => $remoteSql,
            'size_bytes' => $verified['size_bytes'],
            'sha256' => $verified['manifest']['sha256'],
        ];
    }

    public function status(?string $filename = null): array
    {
        if (! $this->isEnabled()) {
            return [
                'status' => 'not_configured',
                'label' => 'Belum dikonfigurasi',
                'detail' => 'Penyimpanan off-site belum diaktifkan.',
            ];
        }

        try {
            $backupManager = app(DatabaseBackupManager::class);
            $filename ??= $this->latestValidFilename($backupManager);
            $verified = $backupManager->verify($filename);
            $disk = Storage::disk($this->diskName());
            [$remoteSql, $remoteManifest] = $this->remotePaths($filename);

            if (! $disk->exists($remoteSql) || ! $disk->exists($remoteManifest)) {
                return [
                    'status' => 'warning',
                    'label' => 'Belum tersinkron',
                    'detail' => 'Salinan off-site untuk backup terbaru belum tersedia.',
                ];
            }

            $remoteHash = $this->hashRemoteFile($disk, $remoteSql);
            $remoteManifestData = json_decode((string) $disk->get($remoteManifest), true);

            if (
                ! hash_equals((string) $verified['manifest']['sha256'], $remoteHash)
                || ! is_array($remoteManifestData)
                || ($remoteManifestData['file'] ?? null) !== $filename
                || ! hash_equals((string) $verified['manifest']['sha256'], (string) ($remoteManifestData['sha256'] ?? ''))
            ) {
                return [
                    'status' => 'error',
                    'label' => 'Integritas tidak cocok',
                    'detail' => 'Hash salinan off-site tidak cocok dengan backup lokal.',
                ];
            }

            return [
                'status' => 'ok',
                'label' => 'Tersinkron dan valid',
                'detail' => 'Salinan off-site cocok dengan checksum backup lokal.',
                'filename' => $filename,
                'disk' => $this->diskName(),
            ];
        } catch (Throwable) {
            return [
                'status' => 'error',
                'label' => 'Tidak dapat diverifikasi',
                'detail' => 'Penyimpanan off-site tidak dapat diakses. Periksa konfigurasi koneksi.',
            ];
        }
    }

    private function latestValidFilename(DatabaseBackupManager $manager): string
    {
        foreach ($manager->verifyAll() as $backup) {
            if (($backup['valid'] ?? false) === true) {
                return (string) $backup['filename'];
            }
        }

        throw new RuntimeException('Belum ada backup lokal valid untuk disinkronkan.');
    }

    private function diskName(): string
    {
        $disk = trim((string) config('koni-backups.remote_disk', ''));

        if ($disk === '' || ! preg_match('/\A[a-zA-Z0-9_-]{1,64}\z/', $disk)) {
            throw new RuntimeException('Nama disk off-site tidak valid.');
        }

        return $disk;
    }

    private function remotePaths(string $filename): array
    {
        $prefix = trim((string) config('koni-backups.remote_prefix', 'koni-klaten/backups'), '/');
        $segments = $prefix === '' ? [] : explode('/', $prefix);

        foreach ($segments as $segment) {
            if ($segment === '.' || $segment === '..' || ! preg_match('/\A[a-zA-Z0-9._-]+\z/', $segment)) {
                throw new RuntimeException('Prefix penyimpanan off-site tidak valid.');
            }
        }

        $base = implode('/', array_filter([$prefix, $filename]));

        return [$base, $base.'.json'];
    }

    private function hashRemoteFile($disk, string $path): string
    {
        $stream = $disk->readStream($path);

        if (! is_resource($stream)) {
            throw new RuntimeException('Salinan off-site tidak dapat dibaca untuk verifikasi.');
        }

        try {
            $context = hash_init('sha256');
            hash_update_stream($context, $stream);

            return hash_final($context);
        } finally {
            fclose($stream);
        }
    }

    private function recordSystemEvent(string $event, string $label, array $details): void
    {
        try {
            AuditLog::query()->create([
                'actor_id' => null,
                'actor_name' => 'System Backup',
                'actor_role' => 'system',
                'event' => $event,
                'subject_label' => Str::limit($label, 255, ''),
                'old_values' => null,
                'new_values' => $details,
                'ip_address' => null,
                'user_agent' => 'Artisan CLI',
                'method' => 'CLI',
                'route_name' => null,
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            // Backup must not fail only because audit logging is unavailable.
        }
    }
}
