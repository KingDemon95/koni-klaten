<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Atlet;
use App\Models\IzinLatihan;
use App\Models\Pelatih;
use App\Support\WorkflowNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class IzinLatihanController extends Controller
{
    private function context(Request $request): array
    {
        $routeName = $request->route()?->getName() ?? '';
        $prefix = str_starts_with($routeName, 'pelatih.') ? 'pelatih' : 'atlet';
        $relation = $prefix === 'pelatih' ? 'pelatih' : 'atlet';
        $profile = $request->user()->{$relation}()->with('cabangOlahraga')->first();

        abort_unless($profile && $profile->status === 'aktif', 403, 'Profil aktif belum terhubung dengan akun Anda.');

        return [
            'profile' => $profile,
            'kind' => $prefix,
            'prefix' => $prefix,
            'layout' => $prefix === 'pelatih' ? 'layouts.pelatih' : 'layouts.atlet',
        ];
    }

    public function index(Request $request): View
    {
        $context = $this->context($request);
        $items = IzinLatihan::query()
            ->where('user_id', $request->user()->id)
            ->with('cabangOlahraga')
            ->latest()
            ->paginate(10);

        return view('portal.izin-latihan.index', [...$context, 'items' => $items]);
    }

    public function store(Request $request): RedirectResponse
    {
        $context = $this->context($request);
        $data = $request->validate([
            'tujuan' => ['required', 'string', 'max:180'],
            'keperluan' => ['required', 'string', 'max:2000'],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->store('izin-latihan/'.$context['kind'].'/'.$context['profile']->id, 'local');
        if (!$path) {
            throw new RuntimeException('Dokumen izin latihan gagal disimpan.');
        }

        try {
            $izin = IzinLatihan::create([
                'user_id' => $request->user()->id,
                'cabang_olahraga_id' => $context['profile']->cabang_olahraga_id,
                'jenis_pemohon' => $context['kind'],
                'nama_pemohon' => $context['profile']->nama_lengkap,
                'tujuan' => $data['tujuan'],
                'keperluan' => $data['keperluan'],
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
                'file_path' => $path,
                'file_original_name' => $file->getClientOriginalName(),
                'status' => 'diajukan',
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        WorkflowNotifier::toCaborAdmins(
            (int) $context['profile']->cabang_olahraga_id,
            'Permohonan izin latihan baru',
            $context['profile']->nama_lengkap.' mengajukan izin latihan untuk "'.$izin->tujuan.'".',
            route('cabor.izin-latihan.index', [], false),
            'izin-latihan',
        );

        return redirect()->route($context['prefix'].'.izin-latihan.index')
            ->with('success', 'Dokumen izin latihan berhasil diunggah.');
    }

    public function download(Request $request, IzinLatihan $izinLatihan): StreamedResponse
    {
        $this->context($request);
        $owned = IzinLatihan::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($izinLatihan->id);
        abort_unless(Storage::disk('local')->exists($owned->file_path), 404);

        return Storage::disk('local')->download($owned->file_path, $owned->file_original_name);
    }

    public function destroy(Request $request, IzinLatihan $izinLatihan): RedirectResponse
    {
        $context = $this->context($request);
        $owned = IzinLatihan::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($izinLatihan->id);
        abort_unless($owned->status === 'diajukan', 403, 'Dokumen yang sudah diproses tidak dapat dihapus.');

        $path = $owned->file_path;
        $owned->delete();
        Storage::disk('local')->delete($path);

        return redirect()->route($context['prefix'].'.izin-latihan.index')
            ->with('success', 'Dokumen izin latihan berhasil dihapus.');
    }
}
