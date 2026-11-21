<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteAppearanceSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class WebsiteAppearanceController extends Controller
{
    public function edit(): View
    {
        $setting = WebsiteAppearanceSetting::current();

        return view('admin.website-appearance.edit', [
            'setting' => $setting,
            'draftConfig' => $setting->draft_config ?? WebsiteAppearanceSetting::defaultConfig(),
            'publishedConfig' => $setting->published_config ?? WebsiteAppearanceSetting::defaultConfig(),
            'pages' => WebsiteAppearanceSetting::pageDefinitions(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'header_background' => ['required', Rule::in(['#FFFFFF', '#F7F8FA', '#FFF5F5'])],
            'footer_background' => ['required', Rule::in(['#17191F', '#20242B', '#3B1015'])],
            'font_family' => ['required', Rule::in(['figtree', 'inter', 'system'])],
            'backdrops' => ['sometimes', 'array'],
            'backdrops.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'overlay_opacity' => ['sometimes', 'array'],
            'overlay_opacity.*' => ['required', 'integer', 'min:10', 'max:90'],
            'background_position' => ['sometimes', 'array'],
            'background_position.*' => ['required', Rule::in(['center', 'top', 'bottom'])],
            'remove_backdrop' => ['sometimes', 'array'],
            'remove_backdrop.*' => ['nullable', 'boolean'],
        ]);

        $setting = WebsiteAppearanceSetting::current();
        $oldDraft = $setting->draft_config ?? WebsiteAppearanceSetting::defaultConfig();
        $draft = $oldDraft;
        $storedPaths = [];

        $draft['primary_color'] = $validated['primary_color'];
        $draft['secondary_color'] = $validated['secondary_color'];
        $draft['header_background'] = $validated['header_background'];
        $draft['footer_background'] = $validated['footer_background'];
        $draft['font_family'] = $validated['font_family'];
        $draft['pages'] ??= [];

        foreach (WebsiteAppearanceSetting::pageDefinitions() as $pageKey => $label) {
            $page = $draft['pages'][$pageKey] ?? [
                'backdrop_path' => null,
                'overlay_opacity' => 62,
                'background_position' => 'center',
            ];

            $file = $request->file('backdrops.'.$pageKey);
            if ($file) {
                $path = $file->store('website-backdrops/'.$pageKey, 'public');
                if (! $path) {
                    foreach ($storedPaths as $storedPath) {
                        Storage::disk('public')->delete($storedPath);
                    }

                    return back()->withInput()->withErrors([
                        'backdrops.'.$pageKey => 'Gambar '.$label.' gagal disimpan. Silakan coba kembali.',
                    ]);
                }

                $storedPaths[] = $path;
                $page['backdrop_path'] = $path;
            } elseif ($request->boolean('remove_backdrop.'.$pageKey)) {
                $page['backdrop_path'] = null;
            }

            $page['overlay_opacity'] = (int) data_get($validated, 'overlay_opacity.'.$pageKey, $page['overlay_opacity'] ?? 62);
            $page['background_position'] = data_get($validated, 'background_position.'.$pageKey, $page['background_position'] ?? 'center');
            $draft['pages'][$pageKey] = $page;
        }

        try {
            $setting->update([
                'draft_config' => $draft,
                'updated_by' => $request->user()->id,
            ]);
        } catch (Throwable $exception) {
            foreach ($storedPaths as $storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $exception;
        }

        $this->deleteUnreferencedPaths(
            $this->pathsInConfig($oldDraft),
            array_merge(
                $this->pathsInConfig($draft),
                $this->pathsInConfig($setting->published_config ?? []),
            ),
        );

        return redirect()->route('admin.website-appearance.edit')
            ->with('success', 'Draft tampilan website berhasil disimpan. Perubahan belum tampil di website publik.');
    }

    public function publish(Request $request): RedirectResponse
    {
        $setting = WebsiteAppearanceSetting::current();
        $oldPublished = $setting->published_config ?? WebsiteAppearanceSetting::defaultConfig();
        $newPublished = $setting->draft_config ?? WebsiteAppearanceSetting::defaultConfig();

        $setting->update([
            'published_config' => $newPublished,
            'published_at' => now(),
            'updated_by' => $request->user()->id,
        ]);

        $this->deleteUnreferencedPaths(
            $this->pathsInConfig($oldPublished),
            $this->pathsInConfig($newPublished),
        );

        return redirect()->route('admin.website-appearance.edit')
            ->with('success', 'Tampilan website berhasil dipublikasikan ke seluruh halaman publik.');
    }

    public function preview(string $page): RedirectResponse
    {
        $routes = [
            'home' => 'public.home',
            'cabor' => 'public.cabor.index',
            'struktur' => 'public.struktur.index',
            'agenda' => 'public.agenda.index',
            'statistik' => 'public.statistik.index',
            'berita' => 'public.berita.index',
            'dokumentasi' => 'public.dokumentasi.index',
        ];

        abort_unless(isset($routes[$page]), 404);

        return redirect()->to(route($routes[$page]).'?appearance_preview=1');
    }

    private function pathsInConfig(array $config): array
    {
        return collect(data_get($config, 'pages', []))
            ->pluck('backdrop_path')
            ->filter(fn ($path): bool => is_string($path) && $path !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function deleteUnreferencedPaths(array $candidates, array $retained): void
    {
        $unused = array_values(array_diff($candidates, $retained));

        if ($unused !== []) {
            Storage::disk('public')->delete($unused);
        }
    }
}
