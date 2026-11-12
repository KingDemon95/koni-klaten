<?php

namespace App\Http\Controllers\Cabor;

use App\Http\Controllers\Controller;
use App\Models\IzinLatihan;
use App\Support\WorkflowNotifier;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IzinLatihanController extends Controller
{
    private function caborId(Request $request): int
    {
        $id = $request->user()->cabang_olahraga_id;
        abort_unless($id, 403, 'Akun Admin Cabor belum terhubung dengan cabang olahraga.');

        return (int) $id;
    }

    public function index(Request $request): View
    {
        $items = IzinLatihan::query()
            ->where('cabang_olahraga_id', $this->caborId($request))
            ->with('user')
            ->latest()
            ->paginate(15);

        return view('cabor.izin-latihan.index', [
            'items' => $items,
            'cabor' => $request->user()->cabangOlahraga,
        ]);
    }

    public function decide(Request $request, IzinLatihan $izinLatihan): RedirectResponse
    {
        $caborId = $this->caborId($request);
        $owned = IzinLatihan::query()
            ->where('cabang_olahraga_id', $caborId)
            ->findOrFail($izinLatihan->id);

        abort_unless($owned->status === 'diajukan', 403, 'Permohonan izin ini sudah diproses.');

        $data = $request->validate([
            'status' => ['required', Rule::in(['disetujui', 'ditolak'])],
            'catatan_admin' => ['required_if:status,ditolak', 'nullable', 'string', 'max:2000'],
        ]);

        $owned->update([
            'status' => $data['status'],
            'catatan_admin' => $data['catatan_admin'] ?? null,
            'diproses_oleh' => $request->user()->id,
            'diproses_at' => now(),
        ]);

        $notificationTitle = $data['status'] === 'disetujui'
            ? 'Izin latihan disetujui'
            : 'Izin latihan ditolak';
        $notificationMessage = $data['status'] === 'disetujui'
            ? 'Permohonan izin latihan untuk "'.$owned->tujuan.'" telah disetujui.'
            : 'Permohonan izin latihan untuk "'.$owned->tujuan.'" ditolak. Alasan: '.($data['catatan_admin'] ?? '-');

        WorkflowNotifier::toUser(
            $owned->user,
            $notificationTitle,
            $notificationMessage,
            route($owned->jenis_pemohon.'.izin-latihan.index', [], false),
            'izin-latihan',
        );

        $message = $data['status'] === 'disetujui'
            ? 'Permohonan izin latihan berhasil disetujui.'
            : 'Permohonan izin latihan ditolak. Alasan penolakan dikirim ke pemohon.';

        return redirect()->route('cabor.izin-latihan.index')->with('success', $message);
    }

    public function download(Request $request, IzinLatihan $izinLatihan): StreamedResponse
    {
        $owned = IzinLatihan::query()
            ->where('cabang_olahraga_id', $this->caborId($request))
            ->findOrFail($izinLatihan->id);
        abort_unless(Storage::disk('local')->exists($owned->file_path), 404);

        return Storage::disk('local')->download($owned->file_path, $owned->file_original_name);
    }
}
