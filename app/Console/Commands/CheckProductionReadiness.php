<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

class CheckProductionReadiness extends Command
{
    protected $signature = 'koni:production-check';

    protected $description = 'Periksa konfigurasi dan layanan wajib sebelum deployment produksi';

    public function handle(): int
    {
        $checks = [];
        $this->addCheck($checks, 'APP_ENV', app()->environment('production'), 'APP_ENV production', 'APP_ENV harus production.');
        $this->addCheck($checks, 'APP_DEBUG', ! (bool) config('app.debug'), 'APP_DEBUG nonaktif', 'APP_DEBUG harus false.');
        $this->addCheck($checks, 'APP_KEY', $this->hasValidAppKey(), 'APP_KEY tersedia dan valid', 'APP_KEY belum valid. Buat kunci aplikasi 32 byte.');
        $this->addCheck($checks, 'APP_URL', parse_url((string) config('app.url'), PHP_URL_SCHEME) === 'https', 'APP_URL menggunakan HTTPS', 'APP_URL harus menggunakan HTTPS.');
        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $domain = strtolower(trim((string) (getenv('APP_DOMAIN') ?: '')));
        $domainReady = $domain !== ''
            && $domain === $appHost
            && ! str_ends_with($domain, '.example')
            && ! in_array($domain, ['localhost', '127.0.0.1'], true);
        $this->addCheck($checks, 'APP_DOMAIN', $domainReady, 'Domain Caddy sesuai dengan APP_URL', 'APP_DOMAIN harus sama dengan host APP_URL dan bukan domain contoh.');
        $this->addCheck($checks, 'SESSION_SECURE_COOKIE', filter_var(config('session.secure'), FILTER_VALIDATE_BOOLEAN), 'Cookie sesi hanya melalui HTTPS', 'SESSION_SECURE_COOKIE harus true.');

        $secretsReady = $this->isConfiguredSecret((string) config('database.connections.mysql.password'))
            && $this->isConfiguredSecret((string) (getenv('DB_ROOT_PASSWORD') ?: ''))
            && $this->isConfiguredSecret((string) config('database.redis.default.password'));
        $this->addCheck($checks, 'Service passwords', $secretsReady, 'Password database dan Redis telah diatur', 'DB_PASSWORD, DB_ROOT_PASSWORD, dan REDIS_PASSWORD harus memakai secret unik minimal 24 karakter.');

        $databaseReady = false;
        if (config('database.default') === 'mysql') {
            try {
                DB::connection()->select('SELECT 1');
                $databaseReady = true;
            } catch (Throwable) {
                $databaseReady = false;
            }
        }
        $this->addCheck($checks, 'Database', $databaseReady, 'Koneksi MySQL berhasil', 'DB_CONNECTION harus mysql dan database harus dapat dihubungi.');

        $redisReady = false;
        try {
            Redis::connection()->ping();
            $redisReady = true;
        } catch (Throwable) {
            $redisReady = false;
        }
        $this->addCheck($checks, 'Redis', $redisReady, 'Koneksi Redis berhasil', 'Redis tidak dapat dihubungi. Periksa host, port, password, dan layanan.');

        $this->addCheck(
            $checks,
            'Vite assets',
            is_file(public_path('build/manifest.json')) && ! is_file(public_path('hot')),
            'Asset frontend tersedia tanpa dev server',
            'Build frontend belum tersedia atau file public/hot masih ada.'
        );

        $storagePaths = [
            storage_path('app/private/backups'),
            storage_path('app/public'),
            storage_path('framework'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];
        $storageReady = true;
        foreach ($storagePaths as $path) {
            if (! is_dir($path) || ! is_writable($path)) {
                $storageReady = false;
                break;
            }
        }
        $this->addCheck($checks, 'Storage', $storageReady, 'Storage dan bootstrap/cache dapat ditulis', 'Storage atau bootstrap/cache tidak dapat ditulis.');

        $this->addCheck($checks, 'Queue', config('queue.default') === 'redis', 'Queue menggunakan Redis', 'QUEUE_CONNECTION harus redis pada konfigurasi produksi.');
        $this->addCheck($checks, 'Cache', config('cache.default') === 'redis', 'Cache menggunakan Redis', 'CACHE_STORE harus redis pada konfigurasi produksi.');
        $this->addCheck($checks, 'Session', config('session.driver') === 'redis', 'Session menggunakan Redis', 'SESSION_DRIVER harus redis pada konfigurasi produksi.');
        $this->addCheck($checks, 'Logging', config('logging.default') === 'stderr', 'Log dikirim ke stderr/container logs', 'LOG_CHANNEL harus stderr pada konfigurasi produksi.');

        $failed = count(array_filter($checks, fn (array $check): bool => ! $check['passed']));

        foreach ($checks as $check) {
            $this->line(($check['passed'] ? '[OK] ' : '[PERIKSA] ').$check['label'].': '.$check['message']);
        }

        $this->newLine();
        $this->line('Pemeriksaan selesai: '.(count($checks) - $failed).' OK, '.$failed.' perlu diperbaiki.');

        if ($failed > 0) {
            $this->warn('Aplikasi belum memenuhi seluruh pemeriksaan deployment produksi.');

            return self::FAILURE;
        }

        $this->info('Aplikasi memenuhi pemeriksaan teknis produksi.');

        return self::SUCCESS;
    }

    private function isConfiguredSecret(string $secret): bool
    {
        $secret = trim($secret);

        return strlen($secret) >= 24
            && ! preg_match('/ganti_dengan|change.?me|example|password|secret/i', $secret);
    }

    private function hasValidAppKey(): bool
    {
        $key = (string) config('app.key');

        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);

            return is_string($decoded) && strlen($decoded) === 32;
        }

        return strlen($key) === 32;
    }

    /**
     * @param  array<int, array{label: string, passed: bool, message: string}>  $checks
     */
    private function addCheck(array &$checks, string $label, bool $passed, string $success, string $failure): void
    {
        $checks[] = [
            'label' => $label,
            'passed' => $passed,
            'message' => $passed ? $success : $failure,
        ];
    }
}
