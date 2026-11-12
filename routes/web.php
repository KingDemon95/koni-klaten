<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cabor\CaborPanelController;
use App\Http\Controllers\Staff\StaffPanelController;
use App\Http\Controllers\Atlet\AtletPanelController;
use App\Http\Controllers\Pelatih\PelatihPanelController;
use App\Http\Controllers\PublicPortalController;
use App\Http\Controllers\PublicAgendaController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\TemplateSuratController;
use App\Http\Controllers\Portal\PrestasiController as PortalPrestasiController;
use App\Http\Controllers\Cabor\PrestasiController as CaborPrestasiController;
use App\Http\Controllers\Portal\IzinLatihanController;
use App\Http\Controllers\Cabor\IzinLatihanController as CaborIzinLatihanController;
use App\Http\Controllers\PublikasiController;
use App\Http\Controllers\PublicPublikasiController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ProfilKoniController;
use App\Http\Controllers\Admin\PrestasiMonitoringController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;

Route::get('/', [PublicPortalController::class, 'home'])->name('public.home');
Route::get('/sitemap.xml', \App\Http\Controllers\SitemapController::class)->name('public.sitemap');
Route::get('/cabang-olahraga', [PublicPortalController::class, 'caborIndex'])->name('public.cabor.index');
Route::get('/cabang-olahraga/{cabangOlahraga}', [PublicPortalController::class, 'caborShow'])
    ->whereNumber('cabangOlahraga')
    ->name('public.cabor.show');
Route::get('/struktur-pengurus', [PublicPortalController::class, 'strukturIndex'])->name('public.struktur.index');
Route::get('/statistik-prestasi', [\App\Http\Controllers\PublicStatistikController::class, 'index'])->name('public.statistik.index');
Route::get('/agenda', [PublicAgendaController::class, 'index'])->name('public.agenda.index');
Route::get('/agenda/{agenda:slug}', [PublicAgendaController::class, 'show'])->name('public.agenda.show');
Route::get('/berita', [PublicPublikasiController::class, 'beritaIndex'])->name('public.berita.index');
Route::get('/berita/{publikasi:slug}', [PublicPublikasiController::class, 'beritaShow'])->name('public.berita.show');
Route::get('/dokumentasi', [PublicPublikasiController::class, 'dokumentasiIndex'])->name('public.dokumentasi.index');
Route::get('/dokumentasi/{publikasi:slug}', [PublicPublikasiController::class, 'dokumentasiShow'])->name('public.dokumentasi.show');
Route::get('/publikasi/{publikasi:slug}/media/{variant?}', [PublicPublikasiController::class, 'media'])->whereIn('variant', ['media', 'thumbnail'])->name('public.publikasi.media');

Route::middleware('auth')->group(function () {

    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifikasi/{notification}/buka', [NotificationController::class, 'open'])->name('notifications.open');

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

    // Dashboard Admin KONI dengan ringkasan data langsung dari database.
    Route::get('/dashboard/admin-koni', AdminDashboardController::class)
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

    Route::get('prestasi', [PrestasiMonitoringController::class, 'index'])->name('prestasi.index');
    Route::get('prestasi/{prestasi}/bukti', [PrestasiMonitoringController::class, 'download'])->name('prestasi.bukti');

    Route::post('agenda/{agenda}/publish', [AgendaController::class, 'publish'])->name('agenda.publish');
    Route::post('agenda/{agenda}/revisi', [AgendaController::class, 'requestRevision'])->name('agenda.revisi');
    Route::resource('agenda', AgendaController::class)->except(['show']);


    // Nama dan URL Persuratan Admin KONI dibuat eksplisit agar konsisten dengan menu panel.
    Route::get('persuratan', [SuratController::class, 'index'])->name('persuratan.index');
    Route::get('persuratan/{surat}', [SuratController::class, 'show'])->name('persuratan.show');
    Route::post('persuratan/{surat}/keputusan', [SuratController::class, 'decide'])->name('persuratan.keputusan');
    Route::get('persuratan/{surat}/file/{jenis}', [SuratController::class, 'download'])->name('persuratan.file');

    // Alias URL lama dipertahankan agar tautan yang sudah pernah digunakan tidak langsung rusak.
    Route::get('surat', [SuratController::class, 'index'])->name('surat.index');
    Route::get('surat/{surat}', [SuratController::class, 'show'])->name('surat.show');
    Route::post('surat/{surat}/keputusan', [SuratController::class, 'decide'])->name('surat.keputusan');
    Route::get('surat/{surat}/file/{jenis}', [SuratController::class, 'download'])->name('surat.file');

    Route::get('templates-surat', [TemplateSuratController::class, 'index'])->name('templates.index');
    Route::post('templates-surat', [TemplateSuratController::class, 'store'])->name('templates.store');
    Route::put('templates-surat/{template}', [TemplateSuratController::class, 'update'])->name('templates.update');
    Route::delete('templates-surat/{template}', [TemplateSuratController::class, 'destroy'])->name('templates.destroy');
    Route::get('templates-surat/{template}/download', [TemplateSuratController::class, 'download'])->name('templates.download');


    Route::get('publikasi/{publikasi}/media/{variant?}', [PublikasiController::class, 'media'])
        ->whereIn('variant', ['media', 'thumbnail'])->name('publikasi.media');
    Route::post('publikasi/{publikasi}/publish', [PublikasiController::class, 'publish'])->name('publikasi.publish');
    Route::resource('publikasi', PublikasiController::class);


    Route::resource(
        'pengguna',
        \App\Http\Controllers\Admin\PenggunaController::class
    )->except(['show']);


    Route::get('laporan', [ReportController::class, 'index'])->name('laporan.index');
    Route::get('laporan/export/{jenis}', [ReportController::class, 'export'])
        ->whereIn('jenis', ['cabor', 'prestasi'])
        ->name('laporan.export');

    Route::get('/pengaturan', [ProfilKoniController::class, 'edit'])->name('settings');
    Route::put('/pengaturan', [ProfilKoniController::class, 'update'])->name('settings.update');

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

    Route::post('agenda/{agenda}/ajukan', [AgendaController::class, 'submit'])->name('agenda.submit');
    Route::resource('agenda', AgendaController::class)->except(['show']);

    Route::resource('surat', SuratController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('surat/{surat}/ajukan', [SuratController::class, 'submit'])->name('surat.submit');
    Route::get('surat/{surat}/file/{jenis}', [SuratController::class, 'download'])->name('surat.file');

    Route::get('templates-surat', [TemplateSuratController::class, 'index'])->name('templates.index');
    Route::get('templates-surat/{template}/download', [TemplateSuratController::class, 'download'])->name('templates.download');

    Route::get('prestasi', [CaborPrestasiController::class, 'index'])->name('prestasi.index');
    Route::post('prestasi/{prestasi}/review', [CaborPrestasiController::class, 'review'])->name('prestasi.review');
    Route::get('prestasi/{prestasi}/bukti', [CaborPrestasiController::class, 'download'])->name('prestasi.bukti');

    Route::get('laporan', [ReportController::class, 'index'])->name('laporan.index');
    Route::get('laporan/export/{jenis}', [ReportController::class, 'export'])
        ->whereIn('jenis', ['cabor', 'prestasi'])
        ->name('laporan.export');

    Route::get('izin-latihan', [CaborIzinLatihanController::class, 'index'])->name('izin-latihan.index');
    Route::post('izin-latihan/{izinLatihan}/keputusan', [CaborIzinLatihanController::class, 'decide'])->name('izin-latihan.keputusan');
    Route::get('izin-latihan/{izinLatihan}/download', [CaborIzinLatihanController::class, 'download'])->name('izin-latihan.download');

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

    Route::post('agenda/{agenda}/publish', [AgendaController::class, 'publish'])->name('agenda.publish');
    Route::post('agenda/{agenda}/revisi', [AgendaController::class, 'requestRevision'])->name('agenda.revisi');
    Route::resource('agenda', AgendaController::class)->except(['show']);

    Route::get('surat', [SuratController::class, 'index'])->name('surat.index');
    Route::get('surat/{surat}', [SuratController::class, 'show'])->name('surat.show');
    Route::post('surat/{surat}/revisi', [SuratController::class, 'requestRevision'])->name('surat.revisi');
    Route::post('surat/{surat}/teruskan', [SuratController::class, 'forward'])->name('surat.teruskan');
    Route::post('surat/{surat}/sampaikan', [SuratController::class, 'deliver'])->name('surat.sampaikan');
    Route::get('surat/{surat}/file/{jenis}', [SuratController::class, 'download'])->name('surat.file');

    Route::get('templates-surat', [TemplateSuratController::class, 'index'])->name('templates.index');
    Route::post('templates-surat', [TemplateSuratController::class, 'store'])->name('templates.store');
    Route::put('templates-surat/{template}', [TemplateSuratController::class, 'update'])->name('templates.update');
    Route::delete('templates-surat/{template}', [TemplateSuratController::class, 'destroy'])->name('templates.destroy');
    Route::get('templates-surat/{template}/download', [TemplateSuratController::class, 'download'])->name('templates.download');

    Route::get('laporan', [ReportController::class, 'index'])->name('laporan.index');
    Route::get('laporan/export/{jenis}', [ReportController::class, 'export'])
        ->whereIn('jenis', ['cabor', 'prestasi'])
        ->name('laporan.export');

    Route::get('publikasi/{publikasi}/media/{variant?}', [PublikasiController::class, 'media'])
        ->whereIn('variant', ['media', 'thumbnail'])->name('publikasi.media');
    Route::post('publikasi/{publikasi}/publish', [PublikasiController::class, 'publish'])->name('publikasi.publish');
    Route::resource('publikasi', PublikasiController::class);

});

Route::middleware([
        'auth',
        'role:atlet',
    ])
    ->prefix('atlet')
    ->name('atlet.')
    ->group(function () {

    Route::get('/profil', [AtletPanelController::class, 'profil'])->name('profil');
    Route::get('/profil/edit', [AtletPanelController::class, 'editProfil'])->name('profil.edit');
    Route::put('/profil', [AtletPanelController::class, 'updateProfil'])->name('profil.update');

    Route::resource('prestasi', PortalPrestasiController::class)->except(['show']);
    Route::get('prestasi/{prestasi}/bukti', [PortalPrestasiController::class, 'download'])->name('prestasi.bukti');

    Route::get('izin-latihan', [IzinLatihanController::class, 'index'])->name('izin-latihan.index');
    Route::post('izin-latihan', [IzinLatihanController::class, 'store'])->name('izin-latihan.store');
    Route::get('izin-latihan/{izinLatihan}/download', [IzinLatihanController::class, 'download'])->name('izin-latihan.download');
    Route::delete('izin-latihan/{izinLatihan}', [IzinLatihanController::class, 'destroy'])->name('izin-latihan.destroy');

    Route::get('templates-surat', [TemplateSuratController::class, 'index'])->name('templates.index');
    Route::get('templates-surat/{template}/download', [TemplateSuratController::class, 'download'])->name('templates.download');

});

Route::middleware([
        'auth',
        'role:pelatih',
    ])
    ->prefix('pelatih')
    ->name('pelatih.')
    ->group(function () {

    Route::get('/profil', [PelatihPanelController::class, 'profil'])->name('profil');
    Route::get('/profil/edit', [PelatihPanelController::class, 'editProfil'])->name('profil.edit');
    Route::put('/profil', [PelatihPanelController::class, 'updateProfil'])->name('profil.update');

    Route::resource('prestasi', PortalPrestasiController::class)->except(['show']);
    Route::get('prestasi/{prestasi}/bukti', [PortalPrestasiController::class, 'download'])->name('prestasi.bukti');

    Route::get('izin-latihan', [IzinLatihanController::class, 'index'])->name('izin-latihan.index');
    Route::post('izin-latihan', [IzinLatihanController::class, 'store'])->name('izin-latihan.store');
    Route::get('izin-latihan/{izinLatihan}/download', [IzinLatihanController::class, 'download'])->name('izin-latihan.download');
    Route::delete('izin-latihan/{izinLatihan}', [IzinLatihanController::class, 'destroy'])->name('izin-latihan.destroy');

    Route::get('templates-surat', [TemplateSuratController::class, 'index'])->name('templates.index');
    Route::get('templates-surat/{template}/download', [TemplateSuratController::class, 'download'])->name('templates.download');

});
