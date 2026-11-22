<?php

namespace App\Providers;

use App\Models\ProfilKoni;
use App\Models\WebsiteAppearanceSetting;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Atlet;
use App\Models\Pelatih;
use App\Models\CabangOlahraga;
use App\Models\Pengurus;
use App\Models\Prestasi;
use App\Models\Agenda;
use App\Models\Surat;
use App\Models\TemplateSurat;
use App\Models\Publikasi;
use App\Models\IzinLatihan;
use App\Models\InventoryLoan;
use App\Models\InventoryMovement;
use App\Models\User;
use App\Observers\AuditableObserver;
use App\Support\Audit\AuditLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Policies\InventoryCategoryPolicy;
use App\Policies\InventoryItemPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(InventoryItem::class, InventoryItemPolicy::class);
        Gate::policy(InventoryCategory::class, InventoryCategoryPolicy::class);

        foreach ([
            User::class,
            CabangOlahraga::class,
            Atlet::class,
            Pelatih::class,
            Pengurus::class,
            Prestasi::class,
            Agenda::class,
            Surat::class,
            TemplateSurat::class,
            Publikasi::class,
            IzinLatihan::class,
            ProfilKoni::class,
            InventoryCategory::class,
            InventoryItem::class,
            InventoryMovement::class,
            InventoryLoan::class,
        ] as $model) {
            $model::observe(AuditableObserver::class);
        }

        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof User) {
                app(AuditLogger::class)->recordCustom('login', actor: $event->user);
            }
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->user instanceof User) {
                app(AuditLogger::class)->recordCustom('logout', actor: $event->user);
            }
        });

        // Public page templates read these variables while Blade renders the
        // child view, before it renders layouts.public. Registering the
        // composer only on the layout makes $websitePageSections unavailable
        // inside the child templates and causes an undefined "hero" key.
        $shareWebsiteAppearance = function ($view): void {
            $sharedData = request()->attributes->get('website_appearance_view_data');

            if (! is_array($sharedData)) {
                $setting = WebsiteAppearanceSetting::current();
                $isPreview = request()->boolean('appearance_preview')
                    && auth()->user()?->hasRole('admin_koni');

                $config = $isPreview
                    ? ($setting->draft_config ?? WebsiteAppearanceSetting::defaultConfig())
                    : ($setting->published_config ?? WebsiteAppearanceSetting::defaultConfig());
                $config = array_replace_recursive(WebsiteAppearanceSetting::defaultConfig(), $config);

                $pageKey = WebsiteAppearanceSetting::pageKeyForRoute(request()->route()?->getName());
                $defaultPageAppearance = data_get(WebsiteAppearanceSetting::defaultConfig(), 'pages.'.$pageKey, []);
                $pageAppearance = array_replace_recursive(
                    $defaultPageAppearance,
                    data_get($config, 'pages.'.$pageKey, []),
                );
                $fontFamily = match ($config['font_family'] ?? 'figtree') {
                    'inter' => 'Inter, ui-sans-serif, system-ui, sans-serif',
                    'system' => 'system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif',
                    default => 'Figtree, ui-sans-serif, system-ui, sans-serif',
                };

                $sharedData = [
                    'profilKoni' => ProfilKoni::query()->first(),
                    'websiteAppearance' => $config,
                    'websitePageKey' => $pageKey,
                    'websitePageAppearance' => $pageAppearance,
                    'websitePageSections' => $pageAppearance['sections'] ?? [],
                    'websitePageBackdropUrl' => ! empty($pageAppearance['backdrop_path'])
                        ? asset('storage/'.$pageAppearance['backdrop_path'])
                        : null,
                    'websiteFontFamily' => $fontFamily,
                    'websiteAppearancePreview' => (bool) $isPreview,
                ];

                // Both the page and layout composers can run in one request.
                // Cache the resolved data on the request to avoid duplicate queries.
                request()->attributes->set('website_appearance_view_data', $sharedData);
            }

            $view->with($sharedData);
        };

        View::composer([
            'public.home',
            'public.cabor.index',
            'public.cabor.show',
            'public.struktur.index',
            'public.agenda.index',
            'public.agenda.show',
            'public.statistik.index',
            'public.berita.index',
            'public.berita.show',
            'public.dokumentasi.index',
            'public.dokumentasi.show',
            'layouts.public',
        ], $shareWebsiteAppearance);
    }
}
