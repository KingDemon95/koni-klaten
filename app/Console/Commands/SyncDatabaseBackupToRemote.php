<?php

namespace App\Console\Commands;

use App\Support\Backups\RemoteBackupManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncDatabaseBackupToRemote extends Command
{
    protected $signature = 'koni:backup:sync {backup? : Nama file backup lokal yang akan disinkronkan}';

    protected $description = 'Sinkronkan backup database KONI ke penyimpanan off-site';

    public function handle(RemoteBackupManager $manager): int
    {
        if (! $manager->isEnabled()) {
            $this->info('Sinkronisasi off-site belum diaktifkan. Backup lokal tetap berjalan normal.');

            return self::SUCCESS;
        }

        try {
            $result = $manager->sync($this->argument('backup'));
            $this->info('Backup berhasil disinkronkan dan diverifikasi.');
            $this->line('File: '.$result['filename']);
            $this->line('Disk: '.$result['disk']);
            $this->line('Ukuran: '.number_format($result['size_bytes']).' byte');
            $this->line('SHA-256: '.$result['sha256']);

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('KONI off-site backup sync failed.', [
                'exception' => class_basename($exception),
            ]);
            $this->error('Sinkronisasi off-site gagal. Backup lokal tidak dihapus. Periksa konfigurasi penyimpanan.');

            return self::FAILURE;
        }
    }
}
