<?php

namespace App\Http\Controllers;

use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\Pelatih;
use App\Models\Prestasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PublicStatistikController extends Controller
{
    public function index(Request $request): View
    {
        // Statistik publik hanya menggunakan prestasi yang telah diverifikasi
        // dan berasal dari cabang olahraga yang masih aktif.
        $verifiedPrestasis = Prestasi::query()
            ->where('status', 'terverifikasi')
            ->whereHas('cabangOlahraga', fn (Builder $query) => $query->where('status', 'aktif'));

        $availableYears = (clone $verifiedPrestasis)
            ->select('tanggal_prestasi')
            ->distinct()
            ->orderByDesc('tanggal_prestasi')
            ->pluck('tanggal_prestasi')
            ->map(fn ($date) => (int) Carbon::parse($date)->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        $rawYear = $request->query('tahun');
        $selectedYear = is_string($rawYear) && preg_match('/^\d{4}$/D', $rawYear)
            ? (int) $rawYear
            : null;

        if ($selectedYear !== null && ! $availableYears->contains($selectedYear)) {
            $selectedYear = null;
        }

        $filteredPrestasis = clone $verifiedPrestasis;
        if ($selectedYear !== null) {
            $filteredPrestasis->whereYear('tanggal_prestasi', $selectedYear);
        }

        $statistics = [
            'cabor' => CabangOlahraga::query()->where('status', 'aktif')->count(),
            'atlet' => Atlet::query()
                ->where('status', 'aktif')
                ->whereHas('cabangOlahraga', fn (Builder $query) => $query->where('status', 'aktif'))
                ->count(),
            'pelatih' => Pelatih::query()
                ->where('status', 'aktif')
                ->whereHas('cabangOlahraga', fn (Builder $query) => $query->where('status', 'aktif'))
                ->count(),
            'prestasi' => (clone $filteredPrestasis)->count(),
            'emas' => (clone $filteredPrestasis)->where('jenis_medali', 'emas')->count(),
            'perak' => (clone $filteredPrestasis)->where('jenis_medali', 'perak')->count(),
            'perunggu' => (clone $filteredPrestasis)->where('jenis_medali', 'perunggu')->count(),
        ];
        $statistics['medali'] = $statistics['emas'] + $statistics['perak'] + $statistics['perunggu'];

        $ranking = CabangOlahraga::query()
            ->where('status', 'aktif')
            ->withCount([
                'prestasis as prestasi_terverifikasi_count' => function (Builder $query) use ($selectedYear): void {
                    $query->where('status', 'terverifikasi')
                        ->when($selectedYear, fn (Builder $nested) => $nested->whereYear('tanggal_prestasi', $selectedYear));
                },
                'prestasis as medali_emas_count' => function (Builder $query) use ($selectedYear): void {
                    $query->where('status', 'terverifikasi')
                        ->where('jenis_medali', 'emas')
                        ->when($selectedYear, fn (Builder $nested) => $nested->whereYear('tanggal_prestasi', $selectedYear));
                },
                'prestasis as medali_perak_count' => function (Builder $query) use ($selectedYear): void {
                    $query->where('status', 'terverifikasi')
                        ->where('jenis_medali', 'perak')
                        ->when($selectedYear, fn (Builder $nested) => $nested->whereYear('tanggal_prestasi', $selectedYear));
                },
                'prestasis as medali_perunggu_count' => function (Builder $query) use ($selectedYear): void {
                    $query->where('status', 'terverifikasi')
                        ->where('jenis_medali', 'perunggu')
                        ->when($selectedYear, fn (Builder $nested) => $nested->whereYear('tanggal_prestasi', $selectedYear));
                },
            ])
            ->orderByDesc('prestasi_terverifikasi_count')
            ->orderByDesc('medali_emas_count')
            ->orderByDesc('medali_perak_count')
            ->orderByDesc('medali_perunggu_count')
            ->orderBy('nama')
            ->get()
            ->filter(fn (CabangOlahraga $cabor) => $cabor->prestasi_terverifikasi_count > 0)
            ->take(10)
            ->values();

        $tingkatCounts = (clone $filteredPrestasis)
            ->select('tingkat')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('tingkat')
            ->pluck('total', 'tingkat');

        $trendEndYear = max(now()->year, (int) ($availableYears->first() ?? now()->year));
        $trendStartYear = $trendEndYear - 4;
        $trend = collect(range($trendStartYear, $trendEndYear))->map(function (int $year) use ($verifiedPrestasis): array {
            return [
                'tahun' => $year,
                'jumlah' => (clone $verifiedPrestasis)->whereYear('tanggal_prestasi', $year)->count(),
            ];
        });

        $recentPrestasis = (clone $filteredPrestasis)
            ->with([
                'atlet:id,nama_lengkap',
                'pelatih:id,nama_lengkap',
                'cabangOlahraga:id,nama',
            ])
            ->orderByDesc('tanggal_prestasi')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('public.statistik.index', compact(
            'availableYears',
            'selectedYear',
            'statistics',
            'ranking',
            'tingkatCounts',
            'trend',
            'recentPrestasis'
        ));
    }
}
