<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/**
 * Pemeriksaan sebelum demo: hanya membaca konfigurasi, filesystem, dan koneksi DB.
 * Tidak menjalankan migrate, seed, atau membuat akun.
 */
Artisan::command('koni:demo-check', function () {
    $checks = [
        'APP_DEBUG nonaktif' => ! (bool) config('app.debug'),
        'APP_KEY tersedia' => filled(config('app.key')),
        'Asset Vite sudah dibuild (tanpa dev server)' => is_file(public_path('build/manifest.json')) && ! is_file(public_path('hot')),
        'Storage dapat ditulis' => is_writable(storage_path('framework')) && is_writable(storage_path('logs')),
    ];

    try {
        $checks['Koneksi database'] = \Illuminate\Support\Facades\DB::connection()->getPdo() !== null;
    } catch (\Throwable) {
        // Tidak mencetak exception yang mungkin memuat informasi infrastruktur.
        $checks['Koneksi database'] = false;
    }

    $failed = 0;
    foreach ($checks as $label => $passed) {
        $this->line(($passed ? '[OK] ' : '[PERIKSA] ').$label);
        if (! $passed) {
            $failed++;
        }
    }

    $this->newLine();
    $this->comment('Demo publik: gunakan data fiktif, akun demo, dan link HTTPS sementara.');
    $this->comment('Jangan membagikan APP_KEY, file .env, atau dokumen privat.');

    return $failed === 0 ? 0 : 1;
})->purpose('Periksa kesiapan teknis demo KONI tanpa mengubah data');
