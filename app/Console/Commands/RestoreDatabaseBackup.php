<?php

namespace App\Console\Commands;

use App\Support\Backups\DatabaseBackupManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class RestoreDatabaseBackup extends Command
{
    protected $signature = 'koni:backup:restore
                            {backup : Nama file backup yang akan dipulihkan}
                            {--force : Lewati konfirmasi interaktif setelah nama file diverifikasi}';

    protected $description = 'Pulihkan database KONI Klaten dari backup terverifikasi';

    public function handle(DatabaseBackupManager $manager): int
    {
        $filename = (string) $this->argument('backup');

        try {
            $verified = $manager->verify($filename);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Pemulihan pada environment production wajib menggunakan opsi --force secara eksplisit.');

            return self::FAILURE;
        }

        if (! $this->option('force')) {
            $this->warn('PERINGATAN: pemulihan akan menimpa tabel dan data yang tercakup dalam backup pada database aktif.');
            $this->line('Backup: '.$verified['filename']);
            $this->line('Database: '.$verified['manifest']['database']);
            $this->line('Pastikan Anda sudah membuat backup kondisi database saat ini.');

            if (! $this->confirm('Lanjutkan pemulihan database?', false)) {
                $this->comment('Pemulihan dibatalkan. Database tidak diubah.');

                return self::SUCCESS;
            }
        }

        try {
            $manager->restore($filename);
            $this->info('Database berhasil dipulihkan dari '.$filename.'.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('KONI database restore command failed.', [
                'exception' => class_basename($exception),
            ]);

            $this->error('Pemulihan database gagal. Periksa log aplikasi dan MySQL.');

            return self::FAILURE;
        }
    }
}
