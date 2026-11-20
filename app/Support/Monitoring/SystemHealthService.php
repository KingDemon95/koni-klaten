<?php

namespace App\Support\Monitoring;

use App\Support\Backups\DatabaseBackupManager;
use App\Support\Backups\RemoteBackupManager;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

class SystemHealthService
{
    public function report(): array
    {
        $checks = [];
        $this->checkDatabase($checks);
        $this->checkRedis($checks);
        $this->checkLocalStorage($checks);
        $latestBackup = $this->checkLatestBackup($checks);
        $this->checkRemoteBackup($checks, $latestBackup);
        $this->checkScheduler($checks);

        $statuses = array_column($checks, 'status');
        $overall = in_array('error', $statuses, true)
            ? 'error'
            : (in_array('warning', $statuses, true) ? 'warning' : 'ok');

        return [
            'overall' => $overall,
            'overall_label' => match ($overall) {
                'ok' => 'Sistem berjalan normal',
                'warning' => 'Perlu pemeriksaan',
                default => 'Ada pemeriksaan yang gagal',
            },
            'checks' => $checks,
            'checked_at' => now(),
        ];
    }

    private function checkDatabase(array &$checks): void
    {
        try {
            DB::connection()->select('SELECT 1');
            $checks[] = $this->item('database', 'Database', 'ok', 'Koneksi database berhasil.');
        } catch (Throwable) {
            $checks[] = $this->item('database', 'Database', 'error', 'Koneksi database gagal. Periksa layanan database.');
        }
    }

    private function checkRedis(array &$checks): void
    {
        try {
            Redis::connection()->ping();
            $checks[] = $this->item('redis', 'Redis', 'ok', 'Koneksi Redis berhasil.');
        } catch (Throwable) {
            $checks[] = $this->item('redis', 'Redis', 'warning', 'Redis tidak dapat dihubungi. Periksa layanan jika aplikasi menggunakannya.');
        }
    }

    private function checkLocalStorage(array &$checks): void
    {
        $directory = (string) config('koni-backups.directory', storage_path('app/private/backups'));
        $exists = is_dir($directory);
        $writable = $exists && is_writable($directory);

        if (! $exists || ! $writable) {
            $checks[] = $this->item('storage', 'Penyimpanan backup lokal', 'error', 'Direktori backup tidak tersedia atau tidak dapat ditulis.');

            return;
        }

        $freeBytes = @disk_free_space($directory);
        $detail = 'Direktori backup tersedia dan dapat ditulis.';
        $status = 'ok';

        if (is_float($freeBytes) || is_int($freeBytes)) {
            $detail .= ' Ruang kosong: '.number_format((float) $freeBytes / 1024 / 1024, 1).' MB.';
            if ($freeBytes < 512 * 1024 * 1024) {
                $status = 'warning';
                $detail .= ' Ruang kosong kurang dari 512 MB.';
            }
        }

        $checks[] = $this->item('storage', 'Penyimpanan backup lokal', $status, $detail);
    }

    private function checkLatestBackup(array &$checks): ?array
    {
        try {
            $backups = app(DatabaseBackupManager::class)->verifyAll();
            $latest = null;

            foreach ($backups as $backup) {
                if (($backup['valid'] ?? false) === true) {
                    $latest = $backup;
                    break;
                }
            }

            if ($latest === null) {
                $checks[] = $this->item('backup_local', 'Backup lokal terbaru', 'warning', 'Belum ada backup lokal valid.');

                return null;
            }

            $createdAt = CarbonImmutable::parse($latest['created_at']);
            $ageHours = max(0, now()->diffInMinutes($createdAt, true) / 60);
            $limit = max(1, (int) config('koni-backups.max_age_hours', 36));
            $status = $ageHours <= $limit ? 'ok' : 'warning';
            $detail = 'Backup terbaru berusia '.number_format($ageHours, 1).' jam dan checksum valid.';

            if ($status === 'warning') {
                $detail .= ' Batas usia backup adalah '.$limit.' jam.';
            }

            $checks[] = $this->item('backup_local', 'Backup lokal terbaru', $status, $detail);

            return $latest;
        } catch (Throwable) {
            $checks[] = $this->item('backup_local', 'Backup lokal terbaru', 'error', 'Daftar backup tidak dapat diperiksa.');

            return null;
        }
    }

    private function checkRemoteBackup(array &$checks, ?array $latestBackup): void
    {
        $manager = app(RemoteBackupManager::class);

        if (! $manager->isEnabled()) {
            $remote = $manager->status();
        } elseif ($latestBackup === null) {
            $remote = [
                'status' => 'warning',
                'detail' => 'Belum ada backup lokal valid untuk disalin ke penyimpanan off-site.',
            ];
        } else {
            $remote = $manager->status($latestBackup['filename']);
        }

        $checks[] = $this->item(
            'backup_remote',
            'Backup off-site',
            $remote['status'],
            $remote['detail']
        );
    }

    private function checkScheduler(array &$checks): void
    {
        $path = (string) config('koni-backups.scheduler_heartbeat_path');
        $maxAge = max(1, (int) config('koni-backups.scheduler_heartbeat_max_age_minutes', 3));

        if (! is_file($path) || is_link($path)) {
            $checks[] = $this->item('scheduler', 'Scheduler', 'warning', 'Heartbeat belum tercatat. Jalankan scheduler secara persisten.');

            return;
        }

        $modified = filemtime($path);
        $ageMinutes = $modified === false ? PHP_INT_MAX : max(0, (now()->timestamp - $modified) / 60);
        $status = $ageMinutes <= $maxAge ? 'ok' : 'warning';
        $detail = $status === 'ok'
            ? 'Heartbeat scheduler diperbarui '.number_format($ageMinutes, 1).' menit lalu.'
            : 'Heartbeat scheduler sudah kedaluwarsa. Periksa proses schedule:work atau cron.';

        $checks[] = $this->item('scheduler', 'Scheduler', $status, $detail);
    }

    private function item(string $key, string $label, string $status, string $detail): array
    {
        return compact('key', 'label', 'status', 'detail');
    }
}
