<?php

namespace App\Http\Controllers\Atlet;

use App\Http\Controllers\Controller;
use App\Models\Atlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AtletPanelController extends Controller
{
    private function profilMilikAkun(Request $request): ?Atlet
    {
        return $request->user()->atlet()->with('cabangOlahraga')->first();
    }

    public function dashboard(Request $request): View
    {
        $atlet = $this->profilMilikAkun($request);

        return view('atlet.dashboard', [
            'atlet' => $atlet,
            'user' => $request->user(),
        ]);
    }

    public function profil(Request $request): View
    {
        return view('atlet.profil', [
            'atlet' => $this->profilMilikAkun($request),
            'user' => $request->user(),
        ]);
    }

    public function editProfil(Request $request): View|RedirectResponse
    {
        $atlet = $this->profilMilikAkun($request);

        if (!$atlet) {
            return redirect()->route('atlet.profil')
                ->with('error', 'Profil Atlet belum terhubung dengan akun Anda.');
        }

        return view('portal.profil-form', [
            'item' => $atlet,
            'jenis' => 'atlet',
            'routePrefix' => 'atlet',
            'layout' => 'layouts.atlet',
        ]);
    }

    public function updateProfil(Request $request): RedirectResponse
    {
        $atlet = $this->profilMilikAkun($request);

        if (!$atlet) {
            return redirect()->route('atlet.profil')
                ->with('error', 'Profil Atlet belum terhubung dengan akun Anda.');
        }

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'no_telepon' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($data['foto']);
        $oldPhoto = $atlet->foto_path;
        $newPhoto = null;

        try {
            if ($request->hasFile('foto')) {
                $newPhoto = $request->file('foto')->store('atlet', 'public');
                if (!$newPhoto) {
                    throw new RuntimeException('Foto profil gagal disimpan.');
                }
                $data['foto_path'] = $newPhoto;
            }

            $atlet->update($data);
            $request->user()->update(['name' => $data['nama_lengkap']]);
        } catch (Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }
            throw $exception;
        }

        if ($newPhoto && $oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return redirect()->route('atlet.profil')->with('success', 'Biodata Atlet berhasil diperbarui.');
    }
}
