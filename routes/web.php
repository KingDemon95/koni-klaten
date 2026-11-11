<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cabor\CaborPanelController;
use App\Http\Controllers\Staff\StaffPanelController;
use App\Http\Controllers\Atlet\AtletPanelController;
use App\Http\Controllers\Pelatih\PelatihPanelController;

Route::middleware('auth')->group(function () {

    // Pengalihan dashboard berdasarkan role.
    Route::get('/dashboard', function () {

        $user = auth()->user();

        if ($user->hasRole('admin_koni')) {
            return redirect()->route('dashboard.admin-koni');
        }

        if ($user->hasRole('staff_koni')) {
            return redirect()->route('dashboard.staff-koni');
        }

        if ($user->hasRole('admin_cabor')) {
            return redirect()->route('dashboard.admin-cabor');
        }

        if ($user->hasRole('atlet')) {
            return redirect()->route('dashboard.atlet');
        }

        if ($user->hasRole('pelatih')) {
            return redirect()->route('dashboard.pelatih');
        }

        if ($user->hasRole('atlet_pelatih')) {
            return redirect()->route('dashboard.atlet-pelatih');
        }

        abort(403, 'Akun belum memiliki role yang sesuai.');

    })->name('dashboard');

    // Dashboard Admin KONI
    Route::view('/dashboard/admin-koni', 'admin.dashboard')
        ->middleware('role:admin_koni')
        ->name('dashboard.admin-koni');


    // Dashboard Staff KONI
    Route::get(
        '/dashboard/staff-koni',
        [StaffPanelController::class, 'dashboard']
    )
        ->middleware('role:staff_koni')
        ->name('dashboard.staff-koni');


    // Dashboard Admin Cabor
    Route::get(
        '/dashboard/admin-cabor',
        [CaborPanelController::class, 'dashboard']
    )
        ->middleware([
            'role:admin_cabor',
            'milik_cabor',
        ])
        ->name('dashboard.admin-cabor');

    // Dashboard Atlet dan Pelatih
    Route::view('/dashboard/atlet-pelatih', 'dashboard.index')
        ->middleware('role:atlet_pelatih')
        ->name('dashboard.atlet-pelatih');

    
    // Dashboard Atlet
    Route::get(
        '/dashboard/atlet',
        [AtletPanelController::class, 'dashboard']
    )
        ->middleware('role:atlet')
        ->name('dashboard.atlet');

    // Dashboard Pelatih 
    Route::get(
        '/dashboard/pelatih',
        [PelatihPanelController::class, 'dashboard']
    )
        ->middleware('role:pelatih')
        ->name('dashboard.pelatih');

});

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'role:admin_koni'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    Route::resource(
        'cabor',
        \App\Http\Controllers\Admin\CabangOlahragaController::class
    )->except(['show']);


    Route::resource(
        'atlet',
        \App\Http\Controllers\Admin\AtletController::class
    )->except(['show']);

    Route::resource(
        'pelatih',
        \App\Http\Controllers\Admin\PelatihController::class
    )->except(['show']);

    Route::resource(
        'struktur-organisasi',
        \App\Http\Controllers\Admin\StrukturOrganisasiController::class
    )->except(['show']);

    Route::view('/agenda', 'admin.pages.placeholder', [
        'pageTitle' => 'Agenda dan Kejuaraan',
        'pageDescription' => 'Pengelolaan jadwal kegiatan, pertandingan, dan kejuaraan olahraga.',
    ])->name('agenda.index');


    Route::view('/persuratan', 'admin.pages.placeholder', [
        'pageTitle' => 'Manajemen Persuratan',
        'pageDescription' => 'Pengelolaan surat masuk, surat keluar, persetujuan, dan revisi dokumen.',
    ])->name('persuratan.index');


    Route::view('/publikasi', 'admin.pages.placeholder', [
        'pageTitle' => 'Publikasi dan Berita',
        'pageDescription' => 'Pengelolaan berita, dokumentasi, foto, dan informasi kegiatan KONI.',
    ])->name('publikasi.index');


    Route::resource(
        'pengguna',
        \App\Http\Controllers\Admin\PenggunaController::class
    )->except(['show']);


    Route::view('/pengaturan', 'admin.pages.placeholder', [
        'pageTitle' => 'Pengaturan Sistem',
        'pageDescription' => 'Pengaturan identitas organisasi dan konfigurasi aplikasi.',
    ])->name('settings');

});

Route::middleware([
        'auth',
        'role:admin_cabor',
        'milik_cabor',
    ])
    ->prefix('cabor')
    ->name('cabor.')
    ->group(function () {

    Route::get(
        '/atlet',
        [CaborPanelController::class, 'atlet']
    )->name('atlet.index');

    Route::get(
        '/pelatih',
        [CaborPanelController::class, 'pelatih']
    )->name('pelatih.index');

    Route::get(
        '/pengurus',
        [\App\Http\Controllers\Cabor\PengurusController::class, 'index']
    )->name('pengurus.index');

    Route::resource(
        'atlet',
        \App\Http\Controllers\Cabor\AtletController::class
    )->except(['index', 'show']);

    Route::resource(
        'pelatih',
        \App\Http\Controllers\Cabor\PelatihController::class
    )->except(['index', 'show']);
   
    Route::resource(
        'pengurus',
        \App\Http\Controllers\Cabor\PengurusController::class
    )
        ->except(['index', 'show'])
        ->parameters([
            'pengurus' => 'pengurus',
        ]);

});

Route::middleware([
        'auth',
        'role:staff_koni',
    ])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {

    Route::get(
        '/cabor',
        [StaffPanelController::class, 'cabor']
    )->name('cabor.index');

    Route::get(
        '/atlet',
        [StaffPanelController::class, 'atlet']
    )->name('atlet.index');

    Route::get(
        '/pelatih',
        [StaffPanelController::class, 'pelatih']
    )->name('pelatih.index');

    Route::get(
        '/pengurus',
        [StaffPanelController::class, 'pengurus']
    )->name('pengurus.index');

});

Route::middleware([
        'auth',
        'role:atlet',
    ])
    ->prefix('atlet')
    ->name('atlet.')
    ->group(function () {

    Route::get(
        '/profil',
        [AtletPanelController::class, 'profil']
    )->name('profil');

});

Route::middleware([
        'auth',
        'role:pelatih',
    ])
    ->prefix('pelatih')
    ->name('pelatih.')
    ->group(function () {

    Route::get(
        '/profil',
        [PelatihPanelController::class, 'profil']
    )->name('profil');

});
