<?php

namespace App\Console\Commands;

use App\Support\Backups\DatabaseBackupManager;
use Illuminate\Console\Command;

class ListDatabaseBackups extends Command
{
    protected $signature = 'koni:backup:list';

    protected $description = 'Tampilkan daftar backup database dan status integritasnya';

    public function handle(DatabaseBackupManager $manager): int
    {
        $items = $manager->verifyAll();

        if ($items === []) {
            $this->warn('Belum ada file backup database.');

            return self::SUCCESS;
        }

        $this->table(
            ['File backup', 'Ukuran', 'Integritas', 'Keterangan'],
            array_map(
                fn (array $item): array => [
                    $item['filename'],
                    number_format($item['size_bytes']).' byte',
                    $item['valid'] ? 'VALID' : 'GAGAL',
                    $item['message'],
                ],
                $items
            )
        );

        return collect($items)->every(fn (array $item): bool => $item['valid'])
            ? self::SUCCESS
            : self::FAILURE;
    }
}
