<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\Pelatih;
use App\Models\Pengurus;
use App\Models\Surat;
use App\Models\TemplateSurat;
use Illuminate\View\View;

class PimpinanDashboardController extends Controller
{
    public function ketua(): View
    {
        return view('ketua.dashboard', $this->summary('admin'));
    }

    public function sekretaris(): View
    {
        return view('sekretaris.dashboard', $this->summary('sekretaris'));
    }

    private function summary(string $routePrefix): array
    {
        return [
            'routePrefix' => $routePrefix,
            'stats' => [
                'cabor' => CabangOlahraga::query()->where('status', 'aktif')->count(),
                'atlet' => Atlet::query()->where('status', 'aktif')->count(),
                'pelatih' => Pelatih::query()->where('status', 'aktif')->count(),
                'pengurus' => Pengurus::query()->where('status', 'aktif')->count(),
                'surat_menunggu' => Surat::query()->where('status', Surat::STATUS_DITERUSKAN)->count(),
                'surat_disampaikan' => Surat::query()->where('status', Surat::STATUS_MENUNGGU_PENYAMPAIAN)->count(),
                'agenda_terbit' => Agenda::query()->where('status', Agenda::STATUS_PUBLISHED)->count(),
                'template_aktif' => TemplateSurat::query()->where('is_active', true)->count(),
            ],
            'recentSurats' => Surat::query()
                ->with(['cabangOlahraga:id,nama', 'creator:id,name', 'staff:id,name', 'decisionMaker:id,name'])
                ->whereIn('status', [
                    Surat::STATUS_DITERUSKAN,
                    Surat::STATUS_PERLU_REVISI_STAFF,
                    Surat::STATUS_MENUNGGU_PENYAMPAIAN,
                ])
                ->latest('updated_at')
                ->limit(6)
                ->get(),
        ];
    }
}
