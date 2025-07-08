<?php

namespace App\Providers;

use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\RencanaAnggaranKas;
use App\Models\Seksi;
use App\Models\SubKegiatan;
use App\Models\User;
use App\Observers\RencanaAnggaranKasObserver;
use App\Policies\KegiatanPolicy;
use App\Policies\ProgramPolicy;
use App\Policies\SubKegiatanPolicy;
use App\Policies\UserPolicy;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Services\PerformanceOptimizationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use App\Observers\SeksiCacheObserver;
use App\Services\CacheService;
use Filament\Pages\Dashboard;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(PerformanceOptimizationService::class);
        $this->app->singleton(CacheService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Program::class, ProgramPolicy::class);
        Gate::policy(Kegiatan::class, KegiatanPolicy::class);
        Gate::policy(SubKegiatan::class, SubKegiatanPolicy::class);


        RencanaAnggaranKas::observe(RencanaAnggaranKasObserver::class);

        Seksi::observe(SeksiCacheObserver::class);
        // Disable lazy loading in development to catch N+1 queries
        // if (app()->environment('local')) {
        //     Model::preventLazyLoading();
        // }

        // Optimize Eloquent queries in production
        if (app()->environment('production')) {
            Model::preventSilentlyDiscardingAttributes();
            Model::preventAccessingMissingAttributes();
        }

        // Set default cache store if not configured
        if (!config('cache.default')) {
            config(['cache.default' => 'file']);
        }
    }
}
