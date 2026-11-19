<?php

namespace App\Providers;

use App\Models\ProfilKoni;
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

        View::composer('layouts.public', function ($view): void {
            $view->with('profilKoni', ProfilKoni::query()->first());
        });
    }
}
