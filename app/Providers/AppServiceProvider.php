<?php

namespace App\Providers;

use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\SubKegiatan;
use App\Models\User;
use App\Policies\KegiatanPolicy;
use App\Policies\ProgramPolicy;
use App\Policies\SubKegiatanPolicy;
use App\Policies\UserPolicy;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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

        FilamentView::registerRenderHook(
            'panels::head.end',
            fn(): string => Blade::render('
            <style>
                /* Fix sidebar agar tidak terpotong saat scroll */
                .fi-sidebar {
                    position: sticky !important;
                    top: 0 !important;
                    height: 100vh !important;
                    overflow-y: auto !important;
                }
                
                /* Pastikan navigation dalam sidebar bisa scroll */
                .fi-sidebar-nav {
                    height: 100% !important;
                    overflow-y: auto !important;
                }
                
                /* JANGAN ubah main content - biarkan seperti semula */
                /* Hilangkan override margin yang tidak perlu */
            </style>
        ')
        );
    }
}
