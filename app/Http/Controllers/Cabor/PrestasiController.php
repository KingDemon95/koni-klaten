<?php

namespace App\Http\Controllers\Cabor;

use App\Http\Controllers\Controller;
use App\Models\Prestasi;
use App\Support\WorkflowNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrestasiController extends Controller
{
    private function caborId(Request $request): int
    {
        $id = $request->user()->cabang_olahraga_id;
        abort_unless($id, 403, 'Akun Admin Cabor belum terhubung dengan cabang olahraga.');

        return (int) $id;
    }

    public function index(Request $request): View
    {
        $caborId = $this->caborId($request);
        $status = $request->query('status');
        $allowed = ['menunggu_verifikasi', 'terverifikasi', 'perlu_revisi', 'ditolak'];

        $prestasis = Prestasi::query()
            ->where('cabang_olahraga_id', $caborId)
            ->with(['atlet', 'pelatih'])
            ->when(is_string($status) && in_array($status, $allowed, true), fn ($query) => $query->where('status', $status))
            ->orderByRaw("CASE WHEN status = 'menunggu_verifikasi' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('cabor.prestasi.index', [
            'prestasis' => $prestasis,
            'status' => $status,
            'cabor' => $request->user()->cabangOlahraga,
        ]);
    }

    public function review(Request $request, Prestasi $prestasi): RedirectResponse
    {
        $caborId = $this->caborId($request);
        $owned = Prestasi::query()
            ->where('cabang_olahraga_id', $caborId)
            ->findOrFail($prestasi->id);

        abort_unless($owned->status === 'menunggu_verifikasi', 403, 'Prestasi ini sudah diproses.');

        $data = $request->validate([
            'status' => ['required', Rule::in(['terverifikasi', 'perlu_revisi', 'ditolak'])],
            'catatan_verifikasi' => ['required_if:status,perlu_revisi,ditolak', 'nullable', 'string', 'max:2000'],
        ]);

        $owned->update([
            'status' => $data['status'],
            'catatan_verifikasi' => $data['catatan_verifikasi'] ?? null,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        $owned->loadMissing(['atlet.user', 'pelatih.user']);
        $owner = $owned->atlet?->user ?? $owned->pelatih?->user;
        $ownerPrefix = $owned->atlet ? 'atlet' : 'pelatih';
        $notification = match ($data['status']) {
            'terverifikasi' => ['Prestasi terverifikasi', 'Prestasi "'.$owned->nama_kejuaraan.'" telah diverifikasi dan ditampilkan di website publik.'],
            'perlu_revisi' => ['Prestasi perlu revisi', 'Prestasi "'.$owned->nama_kejuaraan.'" perlu diperbaiki. Catatan: '.($data['catatan_verifikasi'] ?? '-')],
            default => ['Prestasi ditolak', 'Prestasi "'.$owned->nama_kejuaraan.'" ditolak. Alasan: '.($data['catatan_verifikasi'] ?? '-')],
        };
        WorkflowNotifier::toUser(
            $owner,
            $notification[0],
            $notification[1],
            route($ownerPrefix.'.prestasi.index', [], false),
            'prestasi',
        );

        $message = match ($data['status']) {
            'terverifikasi' => 'Prestasi berhasil diverifikasi dan sekarang tampil di website publik.',
            'perlu_revisi' => 'Catatan revisi berhasil dikirim kepada pemilik prestasi.',
            default => 'Prestasi ditolak. Alasan penolakan dikirim kepada pemilik prestasi dan data tidak ditampilkan di website publik.',
        };

        return redirect()->route('cabor.prestasi.index')->with('success', $message);
    }

    public function download(Request $request, Prestasi $prestasi): StreamedResponse
    {
        $caborId = $this->caborId($request);
        $owned = Prestasi::query()->where('cabang_olahraga_id', $caborId)->findOrFail($prestasi->id);
        abort_unless(Storage::disk('local')->exists($owned->bukti_path), 404);

        return Storage::disk('local')->download($owned->bukti_path, $owned->bukti_original_name);
    }
}
