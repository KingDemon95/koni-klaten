<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Prestasi;
use App\Support\WorkflowNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PrestasiController extends Controller
{
    private function context(Request $request): array
    {
        $routeName = $request->route()?->getName() ?? '';
        $prefix = str_starts_with($routeName, 'pelatih.') ? 'pelatih' : 'atlet';
        $profile = $request->user()->{$prefix}()->with('cabangOlahraga')->first();
        abort_unless($profile && $profile->status === 'aktif', 403, 'Profil aktif belum terhubung dengan akun Anda.');

        return [
            'profile' => $profile,
            'prefix' => $prefix,
            'layout' => $prefix === 'pelatih' ? 'layouts.pelatih' : 'layouts.atlet',
            'jenis' => $prefix,
        ];
    }

    public function index(Request $request): View
    {
        $context = $this->context($request);
        $prestasis = $context['profile']->prestasis()->latest('tanggal_prestasi')->paginate(10);

        return view('portal.prestasi.index', [...$context, 'prestasis' => $prestasis]);
    }

    public function create(Request $request): View
    {
        return view('portal.prestasi.form', [
            ...$this->context($request),
            'prestasi' => new Prestasi(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $context = $this->context($request);
        $data = $request->validate($this->rules(true));
        $file = $request->file('bukti');
        unset($data['bukti']);
        $path = $file->store('prestasi/'.$context['jenis'].'/'.$context['profile']->id, 'local');

        if (!$path) {
            throw new RuntimeException('Dokumen bukti prestasi gagal disimpan.');
        }

        try {
            $prestasi = $context['profile']->prestasis()->create([
                ...$data,
                'cabang_olahraga_id' => $context['profile']->cabang_olahraga_id,
                'bukti_path' => $path,
                'bukti_original_name' => $file->getClientOriginalName(),
                'status' => 'menunggu_verifikasi',
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        WorkflowNotifier::toCaborAdmins(
            (int) $context['profile']->cabang_olahraga_id,
            'Prestasi baru menunggu verifikasi',
            $context['profile']->nama_lengkap.' mengirim prestasi "'.$prestasi->nama_kejuaraan.'" untuk diperiksa.',
            route('cabor.prestasi.index', [], false),
            'prestasi',
        );

        return redirect()->route($context['prefix'].'.prestasi.index')
            ->with('success', 'Prestasi berhasil dikirim dan menunggu verifikasi Admin Cabor.');
    }

    public function edit(Request $request, Prestasi $prestasi): View
    {
        $context = $this->context($request);
        $owned = $context['profile']->prestasis()->findOrFail($prestasi->id);
        abort_if($owned->status === 'terverifikasi', 403, 'Prestasi yang sudah terverifikasi tidak dapat diubah.');

        return view('portal.prestasi.form', [...$context, 'prestasi' => $owned, 'mode' => 'edit']);
    }

    public function update(Request $request, Prestasi $prestasi): RedirectResponse
    {
        $context = $this->context($request);
        $owned = $context['profile']->prestasis()->findOrFail($prestasi->id);
        abort_if($owned->status === 'terverifikasi', 403, 'Prestasi yang sudah terverifikasi tidak dapat diubah.');

        $data = $request->validate($this->rules(false));
        unset($data['bukti']);
        $oldPath = $owned->bukti_path;
        $newPath = null;

        try {
            if ($request->hasFile('bukti')) {
                $newPath = $request->file('bukti')->store('prestasi/'.$context['jenis'].'/'.$context['profile']->id, 'local');
                if (!$newPath) {
                    throw new RuntimeException('Dokumen bukti prestasi gagal disimpan.');
                }
                $data['bukti_path'] = $newPath;
                $data['bukti_original_name'] = $request->file('bukti')->getClientOriginalName();
            }

            $owned->update([
                ...$data,
                'status' => 'menunggu_verifikasi',
                'catatan_verifikasi' => null,
                'verified_by' => null,
                'verified_at' => null,
            ]);
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }
            throw $exception;
        }

        if ($newPath && $oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        WorkflowNotifier::toCaborAdmins(
            (int) $context['profile']->cabang_olahraga_id,
            'Prestasi diajukan ulang',
            $context['profile']->nama_lengkap.' memperbarui dan mengajukan ulang prestasi "'.$owned->nama_kejuaraan.'".',
            route('cabor.prestasi.index', [], false),
            'prestasi',
        );

        return redirect()->route($context['prefix'].'.prestasi.index')
            ->with('success', 'Prestasi diperbarui dan dikirim ulang untuk verifikasi Admin Cabor.');
    }

    public function destroy(Request $request, Prestasi $prestasi): RedirectResponse
    {
        $context = $this->context($request);
        $owned = $context['profile']->prestasis()->findOrFail($prestasi->id);
        abort_if($owned->status === 'terverifikasi', 403, 'Prestasi yang sudah terverifikasi tidak dapat dihapus.');

        $path = $owned->bukti_path;
        $owned->delete();
        Storage::disk('local')->delete($path);

        return redirect()->route($context['prefix'].'.prestasi.index')->with('success', 'Data prestasi berhasil dihapus.');
    }

    public function download(Request $request, Prestasi $prestasi): StreamedResponse
    {
        $context = $this->context($request);
        $owned = $context['profile']->prestasis()->findOrFail($prestasi->id);
        abort_unless(Storage::disk('local')->exists($owned->bukti_path), 404);

        return Storage::disk('local')->download($owned->bukti_path, $owned->bukti_original_name);
    }

    private function rules(bool $fileRequired): array
    {
        return [
            'nama_kejuaraan' => ['required', 'string', 'max:180'],
            'tingkat' => ['required', Rule::in(['lokal', 'kabupaten', 'provinsi', 'nasional', 'internasional'])],
            'kategori' => ['nullable', 'string', 'max:120'],
            'hasil' => ['required', 'string', 'max:120'],
            'jenis_medali' => ['nullable', Rule::in(['emas', 'perak', 'perunggu'])],
            'penyelenggara' => ['nullable', 'string', 'max:180'],
            'tanggal_prestasi' => ['required', 'date', 'before_or_equal:today'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
            'bukti' => [$fileRequired ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
