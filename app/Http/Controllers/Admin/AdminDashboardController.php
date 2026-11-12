<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\Pelatih;
use App\Models\Prestasi;
use App\Models\Publikasi;
use App\Models\Surat;
use App\Models\SuratActivity;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'cabor_aktif' => CabangOlahraga::query()->where('status', 'aktif')->count(),
            'atlet_aktif' => Atlet::query()->where('status', 'aktif')->count(),
            'pelatih_aktif' => Pelatih::query()->where('status', 'aktif')->count(),
            'pengguna' => User::query()->count(),
            'pengguna_aktif' => User::query()->where('is_active', true)->count(),
            'prestasi_terverifikasi' => Prestasi::query()->where('status', 'terverifikasi')->count(),
            'agenda_terbit' => Agenda::query()->where('status', Agenda::STATUS_PUBLISHED)->count(),
            'surat_menunggu_admin' => Surat::query()->where('status', Surat::STATUS_DITERUSKAN)->count(),
            'publikasi_terbit' => Publikasi::query()->published()->count(),
        ];

        $roleCounts = [
            'Admin KONI' => User::role('admin_koni')->count(),
            'Staff KONI' => User::role('staff_koni')->count(),
            'Admin Cabor' => User::role('admin_cabor')->count(),
            'Atlet' => User::role('atlet')->count(),
            'Pelatih' => User::role('pelatih')->count(),
        ];

        $queues = [
            'agenda_diajukan' => Agenda::query()->where('status', Agenda::STATUS_DIAJUKAN)->count(),
            'prestasi_menunggu' => Prestasi::query()->where('status', 'menunggu_verifikasi')->count(),
            'surat_menunggu_admin' => $stats['surat_menunggu_admin'],
            'surat_perlu_disampaikan' => Surat::query()->where('status', Surat::STATUS_MENUNGGU_PENYAMPAIAN)->count(),
            'publikasi_draft' => Publikasi::query()->where('status', Publikasi::STATUS_DRAFT)->count(),
        ];

        $recentSuratActivities = SuratActivity::query()
            ->with([
                'actor:id,name',
                'surat:id,perihal,cabang_olahraga_id,status',
                'surat.cabangOlahraga:id,nama',
            ])
            ->latest('created_at')
            ->limit(6)
            ->get();

        $recentPublikasis = Publikasi::query()
            ->with('creator:id,name')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'roleCounts',
            'queues',
            'recentSuratActivities',
            'recentPublikasis',
        ));
    }
}
