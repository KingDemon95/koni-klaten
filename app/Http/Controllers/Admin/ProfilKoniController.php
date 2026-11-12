<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfilKoni;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class ProfilKoniController extends Controller
{
    public function edit(): View
    {
        return view('admin.pengaturan.profil-koni', [
            'profil' => ProfilKoni::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_organisasi' => ['required', 'string', 'max:180'],
            'nama_singkat' => ['required', 'string', 'max:80'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:3000'],
            'sejarah' => ['nullable', 'string', 'max:20000'],
            'visi' => ['nullable', 'string', 'max:5000'],
            'misi' => ['nullable', 'string', 'max:5000'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'telepon' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'youtube' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $profil = ProfilKoni::current();
        $logo = $request->file('logo');
        $removeLogo = (bool) ($validated['remove_logo'] ?? false);
        unset($validated['logo'], $validated['remove_logo']);

        $oldPath = null;
        if ($logo) {
            $newPath = $logo->store('profil-koni', 'public');
            if (!$newPath) {
                return back()->withInput()->withErrors(['logo' => 'Logo gagal disimpan. Silakan coba kembali.']);
            }

            $oldPath = $profil->logo_path;
            $validated['logo_path'] = $newPath;
        } elseif ($removeLogo && $profil->logo_path) {
            $oldPath = $profil->logo_path;
            $validated['logo_path'] = null;
        }

        $validated['updated_by'] = $request->user()->id;

        try {
            $profil->update($validated);
        } catch (Throwable $exception) {
            if ($logo && isset($newPath)) {
                Storage::disk('public')->delete($newPath);
            }
            throw $exception;
        }

        if ($oldPath && ($logo || $removeLogo)) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route('admin.settings')
            ->with('success', 'Profil KONI berhasil diperbarui. Perubahan informasi publik langsung diterapkan.');
    }
}
