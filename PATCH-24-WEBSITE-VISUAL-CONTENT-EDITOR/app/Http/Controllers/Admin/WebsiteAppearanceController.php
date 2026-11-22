<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\CabangOlahraga;
use App\Models\Publikasi;
use App\Models\WebsiteAppearanceSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class WebsiteAppearanceController extends Controller
{
    public function edit(Request $request): View
    {
        $setting = WebsiteAppearanceSetting::current();
        $draftConfig = array_replace_recursive(
            WebsiteAppearanceSetting::defaultConfig(),
            $setting->draft_config ?? [],
        );
        $pages = WebsiteAppearanceSetting::pageDefinitions();
        $selectedPage = (string) $request->query('page', 'home');
        abort_unless(array_key_exists($selectedPage, $pages), 404);

        $sectionDefinitions = WebsiteAppearanceSetting::sectionDefinitions($selectedPage);
        uksort($sectionDefinitions, function (string $leftKey, string $rightKey) use ($draftConfig, $selectedPage): int {
            $leftOrder = data_get($draftConfig, 'pages.'.$selectedPage.'.sections.'.$leftKey.'.order', 0);
            $rightOrder = data_get($draftConfig, 'pages.'.$selectedPage.'.sections.'.$rightKey.'.order', 0);

            return ((int) $leftOrder) <=> ((int) $rightOrder);
        });

        return view('admin.website-appearance.edit', [
            'setting' => $setting,
            'draftConfig' => $draftConfig,
            'publishedConfig' => array_replace_recursive(
                WebsiteAppearanceSetting::defaultConfig(),
                $setting->published_config ?? [],
            ),
            'pages' => $pages,
            'selectedPage' => $selectedPage,
            'pageLabel' => $pages[$selectedPage],
            'pageConfig' => $draftConfig['pages'][$selectedPage],
            'sectionDefinitions' => $sectionDefinitions,
            'buttonTargets' => WebsiteAppearanceSetting::buttonTargetOptions(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $pageKey = (string) $request->input('page_key');
        $pageDefinitions = WebsiteAppearanceSetting::pageDefinitions();
        abort_unless(array_key_exists($pageKey, $pageDefinitions), 404);

        $sectionDefinitions = WebsiteAppearanceSetting::sectionDefinitions($pageKey);
        $rules = [
            'page_key' => ['required', Rule::in(array_keys($pageDefinitions))],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'header_background' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'footer_background' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'font_family' => ['required', Rule::in(['figtree', 'inter', 'system'])],
            'global_content' => ['required', 'array:nav_home,nav_profile,nav_cabor,nav_agenda,nav_news,nav_documentation,nav_statistics,nav_structure,portal_button,footer_heading,footer_description,footer_portal,footer_bottom'],
            'global_content.*' => ['nullable', 'string', 'max:3000'],
            'backdrop' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_backdrop' => ['nullable', 'boolean'],
            'overlay_opacity' => ['required', 'integer', 'min:0', 'max:90'],
            'background_position' => ['required', Rule::in(['center', 'top', 'bottom', 'left', 'right'])],
            'hero_background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'title_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'description_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'eyebrow_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'section_background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'section_heading_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'section_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'button_background' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'button_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'sections' => ['required', 'array:'.implode(',', array_keys($sectionDefinitions))],
        ];

        $allowedTargets = array_keys(WebsiteAppearanceSetting::buttonTargetOptions());
        foreach ($sectionDefinitions as $sectionKey => $definition) {
            $rules['sections.'.$sectionKey] = ['required', 'array'];
            $rules['sections.'.$sectionKey.'.visible'] = ['required', 'boolean'];
            $rules['sections.'.$sectionKey.'.order'] = ['required', 'integer', 'min:0', 'max:999'];

            foreach (array_keys($definition['defaults']) as $field) {
                $rules['sections.'.$sectionKey.'.'.$field] = match ($field) {
                    'eyebrow' => ['nullable', 'string', 'max:120'],
                    'title' => ['nullable', 'string', 'max:240'],
                    'description' => ['nullable', 'string', 'max:3000'],
                    'button_label', 'secondary_button_label' => ['nullable', 'string', 'max:100'],
                    'button_target', 'secondary_button_target' => ['nullable', Rule::in($allowedTargets)],
                    default => ['nullable', 'string', 'max:3000'],
                };
            }
        }

        $validated = $request->validate($rules);
        $setting = WebsiteAppearanceSetting::current();
        $oldDraft = array_replace_recursive(
            WebsiteAppearanceSetting::defaultConfig(),
            $setting->draft_config ?? [],
        );
        $draft = $oldDraft;
        $storedPath = null;

        foreach (['primary_color', 'secondary_color', 'header_background', 'footer_background', 'font_family'] as $field) {
            $draft[$field] = $validated[$field];
        }
        $draft['global_content'] = array_replace(
            $draft['global_content'] ?? [],
            $validated['global_content'],
        );

        $page = $draft['pages'][$pageKey];
        foreach ([
            'overlay_opacity', 'background_position', 'hero_background_color', 'title_color',
            'description_color', 'eyebrow_color', 'section_background_color',
            'section_heading_color', 'section_text_color', 'accent_color',
            'button_background', 'button_text_color',
        ] as $field) {
            $page[$field] = $validated[$field];
        }

        $uploadedBackdrop = $request->file('backdrop');
        if ($uploadedBackdrop) {
            $storedPath = $uploadedBackdrop->store('website-backdrops/'.$pageKey, 'public');
            if (! $storedPath) {
                return back()->withInput()->withErrors([
                    'backdrop' => 'Gambar '.$pageDefinitions[$pageKey].' gagal disimpan. Silakan coba kembali.',
                ]);
            }

            $page['backdrop_path'] = $storedPath;
        } elseif ($request->boolean('remove_backdrop')) {
            $page['backdrop_path'] = null;
        }

        foreach ($sectionDefinitions as $sectionKey => $definition) {
            $section = $page['sections'][$sectionKey];
            $input = $validated['sections'][$sectionKey];
            $section['visible'] = (bool) $input['visible'];
            $section['order'] = (int) $input['order'];

            foreach (array_keys($definition['defaults']) as $field) {
                $section[$field] = $input[$field] ?? '';
            }

            $page['sections'][$sectionKey] = $section;
        }

        $draft['pages'][$pageKey] = $page;

        try {
            $setting->update([
                'draft_config' => $draft,
                'updated_by' => $request->user()->id,
            ]);
        } catch (Throwable $exception) {
            if ($storedPath) {
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

        return redirect()->route('admin.website-appearance.edit', ['page' => $pageKey])
            ->with('success', 'Draft halaman '.$pageDefinitions[$pageKey].' berhasil disimpan. Website publik belum berubah sampai Anda memublikasikannya.');
    }

    public function publish(Request $request): RedirectResponse
    {
        $setting = WebsiteAppearanceSetting::current();
        $oldPublished = $setting->published_config ?? WebsiteAppearanceSetting::defaultConfig();
        $newPublished = array_replace_recursive(
            WebsiteAppearanceSetting::defaultConfig(),
            $setting->draft_config ?? [],
        );

        $setting->update([
            'published_config' => $newPublished,
            'published_at' => now(),
            'updated_by' => $request->user()->id,
        ]);

        $this->deleteUnreferencedPaths(
            $this->pathsInConfig($oldPublished),
            $this->pathsInConfig($newPublished),
        );

        $pageKey = (string) $request->input('page_key', 'home');
        if (! array_key_exists($pageKey, WebsiteAppearanceSetting::pageDefinitions())) {
            $pageKey = 'home';
        }

        return redirect()->route('admin.website-appearance.edit', ['page' => $pageKey])
            ->with('success', 'Seluruh draft tampilan website berhasil dipublikasikan. Dashboard internal tidak berubah.');
    }

    public function preview(string $page): RedirectResponse
    {
        $routes = [
            'home' => fn () => route('public.home'),
            'cabor' => fn () => route('public.cabor.index'),
            'cabor_detail' => function (): string {
                $cabor = CabangOlahraga::query()->where('status', 'aktif')->first();

                return $cabor ? route('public.cabor.show', $cabor) : route('public.cabor.index');
            },
            'struktur' => fn () => route('public.struktur.index'),
            'agenda' => fn () => route('public.agenda.index'),
            'agenda_detail' => fn () => ($agenda = Agenda::query()->published()->first())
                ? route('public.agenda.show', $agenda->slug)
                : route('public.agenda.index'),
            'statistik' => fn () => route('public.statistik.index'),
            'berita' => fn () => route('public.berita.index'),
            'berita_detail' => fn () => ($publication = Publikasi::query()->published()->where('jenis', Publikasi::JENIS_BERITA)->first())
                ? route('public.berita.show', $publication->slug)
                : route('public.berita.index'),
            'dokumentasi' => fn () => route('public.dokumentasi.index'),
            'dokumentasi_detail' => fn () => ($publication = Publikasi::query()->published()->whereIn('jenis', [Publikasi::JENIS_FOTO, Publikasi::JENIS_VIDEO])->first())
                ? route('public.dokumentasi.show', $publication->slug)
                : route('public.dokumentasi.index'),
        ];

        abort_unless(array_key_exists($page, $routes), 404);

        return redirect()->to($routes[$page]().'?appearance_preview=1');
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
