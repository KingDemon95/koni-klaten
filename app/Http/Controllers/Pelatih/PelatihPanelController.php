<?php

namespace App\Http\Controllers\Pelatih;

use App\Http\Controllers\Controller;
use App\Models\Pelatih;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PelatihPanelController extends Controller
{
    private function profilMilikAkun(Request $request): ?Pelatih
    {
        return $request->user()->pelatih()->with('cabangOlahraga')->first();
    }

    public function dashboard(Request $request): View
    {
        return view('pelatih.dashboard', [
            'user' => $request->user(),
            'pelatih' => $this->profilMilikAkun($request),
        ]);
    }

    public function profil(Request $request): View
    {
        return view('pelatih.profil', [
            'user' => $request->user(),
            'pelatih' => $this->profilMilikAkun($request),
        ]);
    }

    public function editProfil(Request $request): View|RedirectResponse
    {
        $pelatih = $this->profilMilikAkun($request);

        if (!$pelatih) {
            return redirect()->route('pelatih.profil')
                ->with('error', 'Profil Pelatih belum terhubung dengan akun Anda.');
        }

        return view('portal.profil-form', [
            'item' => $pelatih,
            'jenis' => 'pelatih',
            'routePrefix' => 'pelatih',
            'layout' => 'layouts.pelatih',
        ]);
    }

    public function updateProfil(Request $request): RedirectResponse
    {
        $pelatih = $this->profilMilikAkun($request);

        if (!$pelatih) {
            return redirect()->route('pelatih.profil')
                ->with('error', 'Profil Pelatih belum terhubung dengan akun Anda.');
        }

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'no_telepon' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:150'],
            'spesialisasi' => ['nullable', 'string', 'max:150'],
            'sertifikasi' => ['nullable', 'string', 'max:5000'],
            'pengalaman_tahun' => ['nullable', 'integer', 'min:0', 'max:60'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($data['foto']);
        $oldPhoto = $pelatih->foto_path;
        $newPhoto = null;

        try {
            if ($request->hasFile('foto')) {
                $newPhoto = $request->file('foto')->store('pelatih', 'public');
                if (!$newPhoto) {
                    throw new RuntimeException('Foto profil gagal disimpan.');
                }
                $data['foto_path'] = $newPhoto;
            }

            $pelatih->update($data);
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

        return redirect()->route('pelatih.profil')->with('success', 'Biodata Pelatih berhasil diperbarui.');
    }
}
