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
    }
}
