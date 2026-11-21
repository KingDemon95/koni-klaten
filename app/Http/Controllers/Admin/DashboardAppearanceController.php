<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DashboardSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class DashboardAppearanceController extends Controller
{
    public function edit(): View
    {
        return view('admin.pengaturan.tampilan-dashboard', [
            'settings' => DashboardSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'backdrop' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'overlay_opacity' => ['required', 'integer', 'min:15', 'max:85'],
            'background_position' => ['required', 'in:center,top,bottom'],
            'remove_backdrop' => ['nullable', 'boolean'],
        ]);

        $settings = DashboardSetting::current();
        $newPath = null;
        $oldPath = null;
        $file = $request->file('backdrop');

        if ($file) {
            $newPath = $file->store('dashboard-backdrops', 'public');
            if (!$newPath) {
                return back()->withInput()->withErrors([
                    'backdrop' => 'Gambar backdrop gagal disimpan. Silakan coba kembali.',
                ]);
            }
            $oldPath = $settings->backdrop_path;
        } elseif ($request->boolean('remove_backdrop')) {
            $oldPath = $settings->backdrop_path;
            $newPath = null;
        }

        try {
            $settings->update([
                'backdrop_path' => $file ? $newPath : ($request->boolean('remove_backdrop') ? null : $settings->backdrop_path),
                'overlay_opacity' => $validated['overlay_opacity'],
                'background_position' => $validated['background_position'],
                'updated_by' => $request->user()->id,
            ]);
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            throw $exception;
        }

        if ($oldPath && ($file || $request->boolean('remove_backdrop'))) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('admin.dashboard-appearance.edit')
            ->with('success', 'Tampilan dashboard berhasil diperbarui.');
    }
}
