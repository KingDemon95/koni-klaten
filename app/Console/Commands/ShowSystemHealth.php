<?php

namespace App\Console\Commands;

use App\Support\Monitoring\SystemHealthService;
use Illuminate\Console\Command;

class ShowSystemHealth extends Command
{
    protected $signature = 'koni:system-health';

    protected $description = 'Periksa kesehatan layanan dan backup KONI Klaten';

    public function handle(SystemHealthService $health): int
    {
        $report = $health->report();

        foreach ($report['checks'] as $check) {
            $prefix = match ($check['status']) {
                'ok' => '[OK] ',
                'warning' => '[PERIKSA] ',
                'not_configured' => '[BELUM DIATUR] ',
                default => '[GAGAL] ',
            };

            $this->line($prefix.$check['label'].': '.$check['detail']);
        }

        $this->newLine();
        $this->info($report['overall_label']);

        return $report['overall'] === 'error' ? self::FAILURE : self::SUCCESS;
    }
}
