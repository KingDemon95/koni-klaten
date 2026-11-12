<?php

namespace App\Http\Controllers;

use App\Models\Atlet;
use App\Models\CabangOlahraga;
use App\Models\Pelatih;
use App\Models\Prestasi;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $context = $this->context($request);
        $year = $context['year'];
        $caborId = $context['caborId'];

        $cabors = $this->caborBreakdown($year, $caborId);
        $verifiedPrestasis = $this->verifiedPrestasiQuery($year, $caborId);

        $stats = [
            'cabor_aktif' => $cabors->count(),
            'atlet_aktif' => $this->peopleQuery(Atlet::class, $caborId)->where('status', 'aktif')->count(),
            'pelatih_aktif' => $this->peopleQuery(Pelatih::class, $caborId)->where('status', 'aktif')->count(),
            'prestasi_terverifikasi' => (clone $verifiedPrestasis)->count(),
            'emas' => (clone $verifiedPrestasis)->where('jenis_medali', 'emas')->count(),
            'perak' => (clone $verifiedPrestasis)->where('jenis_medali', 'perak')->count(),
            'perunggu' => (clone $verifiedPrestasis)->where('jenis_medali', 'perunggu')->count(),
            'menunggu_verifikasi' => $this->prestasiInScope($year, $caborId)
                ->where('status', 'menunggu_verifikasi')
                ->count(),
        ];

        $recentPrestasis = (clone $verifiedPrestasis)
            ->with([
                'cabangOlahraga:id,nama',
                'atlet:id,nama_lengkap',
                'pelatih:id,nama_lengkap',
            ])
            ->orderByDesc('tanggal_prestasi')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $availableYears = $this->prestasiInScope(null, $caborId)
            ->pluck('tanggal_prestasi')
            ->map(fn ($date): int => (int) Carbon::parse($date)->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        $user = $request->user();
        $isCaborAdmin = $context['routePrefix'] === 'cabor';
        $caborOptions = $this->activeCaborQuery($isCaborAdmin ? $caborId : null)
            ->orderBy('nama')
            ->get(['id', 'nama']);

        return view('reports.index', [
            ...$context,
            'stats' => $stats,
            'cabors' => $cabors,
            'recentPrestasis' => $recentPrestasis,
            'availableYears' => $availableYears,
            'caborOptions' => $caborOptions,
            'isCaborAdmin' => $isCaborAdmin,
            'cabor' => $isCaborAdmin ? $user->cabangOlahraga : null,
            'layout' => match ($context['routePrefix']) {
                'cabor' => 'layouts.cabor',
                'staff' => 'layouts.staff',
                default => 'layouts.admin',
            },
        ]);
    }

    public function export(Request $request, string $jenis): StreamedResponse
    {
        abort_unless(in_array($jenis, ['cabor', 'prestasi'], true), 404);

        $context = $this->context($request);
        $year = $context['year'];
        $caborId = $context['caborId'];

        if ($jenis === 'cabor') {
            $filename = 'rekap-cabor-koni-klaten-'.now()->format('Ymd-His').'.csv';

            return response()->streamDownload(function () use ($year, $caborId): void {
                $output = fopen('php://output', 'w');
                fwrite($output, "\xEF\xBB\xBF");
                fputcsv($output, [
                    'Cabang Olahraga',
                    'Atlet Aktif',
                    'Pelatih Aktif',
                    'Prestasi Terverifikasi',
                    'Medali Emas',
                    'Medali Perak',
                    'Medali Perunggu',
                ], ',', '"', '');

                foreach ($this->caborBreakdown($year, $caborId) as $cabor) {
                    fputcsv($output, array_map(fn ($value) => $this->safeCsvCell($value), [
                        $cabor->nama,
                        $cabor->atlet_aktif_count,
                        $cabor->pelatih_aktif_count,
                        $cabor->prestasi_terverifikasi_count,
                        $cabor->medali_emas_count,
                        $cabor->medali_perak_count,
                        $cabor->medali_perunggu_count,
                    ]), ',', '"', '');
                }

                fclose($output);
            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        $filename = 'prestasi-terverifikasi-koni-klaten'.($year ? '-'.$year : '').'.csv';

        return response()->streamDownload(function () use ($year, $caborId): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Cabang Olahraga',
                'Nama Atlet/Pelatih',
                'Nama Kejuaraan',
                'Tingkat',
                'Hasil',
                'Jenis Medali',
                'Tanggal Prestasi',
                'Tanggal Verifikasi',
            ], ',', '"', '');

            $this->verifiedPrestasiQuery($year, $caborId)
                ->with([
                    'cabangOlahraga:id,nama',
                    'atlet:id,nama_lengkap',
                    'pelatih:id,nama_lengkap',
                ])
                ->orderBy('tanggal_prestasi')
                ->chunkById(500, function (Collection $prestasis) use ($output): void {
                    foreach ($prestasis as $prestasi) {
                        $namaPemilik = $prestasi->atlet?->nama_lengkap
                            ?? $prestasi->pelatih?->nama_lengkap
                            ?? 'Nama tidak tersedia';

                        fputcsv($output, array_map(fn ($value) => $this->safeCsvCell($value), [
                            $prestasi->cabangOlahraga?->nama ?? '',
                            $namaPemilik,
                            $prestasi->nama_kejuaraan,
                            $prestasi->tingkat,
                            $prestasi->hasil,
                            $prestasi->jenis_medali ?? '',
                            $prestasi->tanggal_prestasi?->format('Y-m-d') ?? '',
                            $prestasi->verified_at?->format('Y-m-d H:i:s') ?? '',
                        ]), ',', '"', '');
                    }
                });

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Validate filters and enforce the current user's data scope.
     * Admin KONI and Staff KONI may select any active branch.
     * Admin Cabor is always restricted to their assigned branch.
     *
     * @return array{year: ?int, caborId: ?int, selectedCaborId: ?int, routePrefix: string}
     */
    private function context(Request $request): array
    {
        $validated = $request->validate([
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:'.now()->year],
            'cabor_id' => ['nullable', 'integer', Rule::exists('cabang_olahraga', 'id')->where(fn ($query) => $query->where('status', 'aktif'))],
        ]);

        /** @var User $user */
        $user = $request->user();
        $routePrefix = $request->routeIs('cabor.*')
            ? 'cabor'
            : ($request->routeIs('staff.*') ? 'staff' : 'admin');
        $isCaborAdmin = $routePrefix === 'cabor';

        $selectedCaborId = isset($validated['cabor_id'])
            ? (int) $validated['cabor_id']
            : null;

        $caborId = $isCaborAdmin
            ? (int) $user->cabang_olahraga_id
            : $selectedCaborId;

        $year = isset($validated['tahun']) ? (int) $validated['tahun'] : null;

        return [
            'year' => $year,
            'caborId' => $caborId,
            'selectedCaborId' => $isCaborAdmin ? $caborId : $selectedCaborId,
            'routePrefix' => $routePrefix,
        ];
    }

    private function activeCaborQuery(?int $caborId): Builder
    {
        return CabangOlahraga::query()
            ->where('status', 'aktif')
            ->when($caborId !== null, fn (Builder $query) => $query->whereKey($caborId));
    }

    /**
     * @param class-string<Atlet|Pelatih> $model
     */
    private function peopleQuery(string $model, ?int $caborId): Builder
    {
        return $model::query()->whereHas(
            'cabangOlahraga',
            fn (Builder $query) => $query
                ->where('status', 'aktif')
                ->when($caborId !== null, fn (Builder $nested) => $nested->whereKey($caborId))
        );
    }

    private function prestasiInScope(?int $year, ?int $caborId): Builder
    {
        return Prestasi::query()
            ->whereHas(
                'cabangOlahraga',
                fn (Builder $query) => $query
                    ->where('status', 'aktif')
                    ->when($caborId !== null, fn (Builder $nested) => $nested->whereKey($caborId))
            )
            ->when($year !== null, fn (Builder $query) => $query->whereYear('tanggal_prestasi', $year));
    }

    private function verifiedPrestasiQuery(?int $year, ?int $caborId): Builder
    {
        return $this->prestasiInScope($year, $caborId)
            ->where('status', 'terverifikasi');
    }

    private function caborBreakdown(?int $year, ?int $caborId): Collection
    {
        return $this->activeCaborQuery($caborId)
            ->withCount([
                'atlets as atlet_aktif_count' => fn (Builder $query) => $query->where('status', 'aktif'),
                'pelatihs as pelatih_aktif_count' => fn (Builder $query) => $query->where('status', 'aktif'),
                'prestasis as prestasi_terverifikasi_count' => fn (Builder $query) => $query
                    ->where('status', 'terverifikasi')
                    ->when($year !== null, fn (Builder $nested) => $nested->whereYear('tanggal_prestasi', $year)),
                'prestasis as medali_emas_count' => fn (Builder $query) => $query
                    ->where('status', 'terverifikasi')
                    ->where('jenis_medali', 'emas')
                    ->when($year !== null, fn (Builder $nested) => $nested->whereYear('tanggal_prestasi', $year)),
                'prestasis as medali_perak_count' => fn (Builder $query) => $query
                    ->where('status', 'terverifikasi')
                    ->where('jenis_medali', 'perak')
                    ->when($year !== null, fn (Builder $nested) => $nested->whereYear('tanggal_prestasi', $year)),
                'prestasis as medali_perunggu_count' => fn (Builder $query) => $query
                    ->where('status', 'terverifikasi')
                    ->where('jenis_medali', 'perunggu')
                    ->when($year !== null, fn (Builder $nested) => $nested->whereYear('tanggal_prestasi', $year)),
            ])
            ->orderBy('nama')
            ->get();
    }

    private function safeCsvCell(mixed $value): string
    {
        $cell = (string) ($value ?? '');

        // Mitigasi CSV/Spreadsheet Formula Injection, termasuk formula
        // yang diawali whitespace atau karakter kontrol.
        if (preg_match('/^[\x00-\x20]*[=+\-@]/', $cell) === 1) {
            return "'".$cell;
        }

        return $cell;
    }
}
