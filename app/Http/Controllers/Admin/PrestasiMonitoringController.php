<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CabangOlahraga;
use App\Models\Prestasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrestasiMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $searchInput = $request->query('q', '');
        $statusInput = $request->query('status', '');
        $caborInput = $request->query('cabor', '');

        abort_unless(is_string($searchInput) && is_string($statusInput) && is_string($caborInput), 422);

        $search = mb_substr(trim($searchInput), 0, 100);
        $allowedStatuses = ['menunggu_verifikasi', 'terverifikasi', 'perlu_revisi', 'ditolak'];
        $status = in_array($statusInput, $allowedStatuses, true) ? $statusInput : '';
        $caborId = ctype_digit($caborInput) ? (int) $caborInput : null;

        $baseQuery = Prestasi::query();
        $query = (clone $baseQuery)->with([
            'atlet:id,nama_lengkap',
            'pelatih:id,nama_lengkap',
            'cabangOlahraga:id,nama',
            'verifier:id,name',
        ]);

        if ($search !== '') {
            $query->where(function ($nested) use ($search): void {
                $nested->where('nama_kejuaraan', 'like', "%{$search}%")
                    ->orWhere('hasil', 'like', "%{$search}%")
                    ->orWhereHas('atlet', fn ($profile) => $profile->where('nama_lengkap', 'like', "%{$search}%"))
                    ->orWhereHas('pelatih', fn ($profile) => $profile->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($caborId) {
            $query->where('cabang_olahraga_id', $caborId);
        }

        $prestasis = $query
            ->orderByRaw("CASE WHEN status = 'menunggu_verifikasi' THEN 0 ELSE 1 END")
            ->orderByDesc('tanggal_prestasi')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'menunggu' => (clone $baseQuery)->where('status', 'menunggu_verifikasi')->count(),
            'terverifikasi' => (clone $baseQuery)->where('status', 'terverifikasi')->count(),
            'perlu_revisi' => (clone $baseQuery)->where('status', 'perlu_revisi')->count(),
            'ditolak' => (clone $baseQuery)->where('status', 'ditolak')->count(),
        ];

        $cabors = CabangOlahraga::query()->orderBy('nama')->get(['id', 'nama']);

        return view('admin.prestasi.index', compact('prestasis', 'stats', 'cabors', 'search', 'status', 'caborId'));
    }

    public function download(Prestasi $prestasi): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($prestasi->bukti_path), 404);

        return Storage::disk('local')->download($prestasi->bukti_path, $prestasi->bukti_original_name);
    }
}
