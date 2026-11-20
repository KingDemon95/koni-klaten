<?php

namespace App\Console\Commands;

use App\Support\Backups\DatabaseBackupManager;
use Illuminate\Console\Command;
use Throwable;

class VerifyDatabaseBackups extends Command
{
    protected $signature = 'koni:backup:verify {backup? : Nama file backup tertentu; kosongkan untuk memeriksa semua}';

    protected $description = 'Verifikasi ukuran dan SHA-256 file backup database';

    public function handle(DatabaseBackupManager $manager): int
    {
        $filename = $this->argument('backup');

        if (is_string($filename) && $filename !== '') {
            try {
                $verified = $manager->verify($filename);
                $this->info('Integritas backup valid: '.$verified['filename']);
                $this->line('SHA-256: '.$verified['manifest']['sha256']);

                return self::SUCCESS;
            } catch (Throwable $exception) {
                $this->error($exception->getMessage());

                return self::FAILURE;
            }
        }

        $items = $manager->verifyAll();

        if ($items === []) {
            $this->warn('Belum ada file backup untuk diverifikasi.');

            return self::SUCCESS;
        }

        $failed = 0;

        foreach ($items as $item) {
            $this->line(($item['valid'] ? '[OK] ' : '[GAGAL] ').$item['filename'].' '.$item['message']);

            if (! $item['valid']) {
                $failed++;
            }
        }

        $this->info('Diperiksa: '.count($items).' file; gagal: '.$failed.'.');

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
