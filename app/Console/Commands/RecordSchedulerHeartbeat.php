<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RecordSchedulerHeartbeat extends Command
{
    protected $signature = 'koni:scheduler-heartbeat';

    protected $description = 'Catat heartbeat scheduler untuk monitoring operasional';

    public function handle(): int
    {
        $path = (string) config('koni-backups.scheduler_heartbeat_path');
        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            $this->error('Direktori heartbeat scheduler tidak dapat dibuat.');

            return self::FAILURE;
        }

        $temporary = $path.'.tmp-'.Str::lower(Str::random(8));

        try {
            if (file_put_contents($temporary, now()->toIso8601String()."\n", LOCK_EX) === false) {
                $this->error('Heartbeat scheduler tidak dapat ditulis.');

                return self::FAILURE;
            }

            if (! chmod($temporary, 0600) || ! rename($temporary, $path)) {
                $this->error('Heartbeat scheduler tidak dapat dipasang.');

                return self::FAILURE;
            }

            @chmod($path, 0600);
            $this->info('Scheduler heartbeat tercatat.');

            return self::SUCCESS;
        } finally {
            if (is_file($temporary)) {
                @unlink($temporary);
            }
        }
    }
}
