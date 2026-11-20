<?php

namespace App\Console\Commands;

use App\Support\Backups\DatabaseBackupManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateDatabaseBackup extends Command
{
    protected $signature = 'koni:backup {--retention= : Jumlah hari penyimpanan backup (1-3650)}';

    protected $description = 'Buat backup database KONI Klaten dan bersihkan backup kedaluwarsa';

    public function handle(DatabaseBackupManager $manager): int
    {
        $retention = $this->option('retention') !== null
            ? filter_var($this->option('retention'), FILTER_VALIDATE_INT)
            : (int) config('koni-backups.retention_days', 30);

        if ($retention === false || $retention < 1 || $retention > 3650) {
            $this->error('Retensi backup harus berupa angka 1 sampai 3650 hari.');

            return self::FAILURE;
        }

        try {
            $backup = $manager->create();
            $removed = $manager->prune($retention);

            $this->info('Backup database berhasil dibuat.');
            $this->line('File: '.$backup['file']);
            $this->line('Ukuran: '.number_format($backup['size_bytes']).' byte');
            $this->line('SHA-256: '.$backup['sha256']);
            $this->line('Retensi: '.$retention.' hari; backup lama dihapus: '.$removed);

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('KONI database backup command failed.', [
                'exception' => class_basename($exception),
            ]);

            $this->error('Backup database gagal. Periksa konfigurasi dan storage/logs/laravel.log.');

            return self::FAILURE;
        }
    }
}
