<?php

namespace App\Providers\Filament;

use App\Filament\Pages\PerencanaanKinerja;
use App\Filament\Pages\RealisasiKinerja;
use App\Filament\Resources\ProgramResource;
use App\Filament\Widgets\GreetingWidget;
use App\Filament\Widgets\ProgramCategoryChart;
use App\Filament\Widgets\ProgramOverviewWidget;
use App\Filament\Widgets\RecentProgramsWidget;
use App\Filament\Widgets\RencanaAnggaranKasWidget;
use App\Filament\Widgets\RiwayatAnggaranKasWidget;
use App\Filament\Widgets\YearSelectorWidget;
use App\Http\Middleware\OwnerUserVerifiedMiddleware;
use App\Models\RencanaAnggaranKas;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Action;
use Filament\Navigation\NavigationGroup;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->breadcrumbs(false)
            ->login()
            ->maxContentWidth('full')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                PerencanaanKinerja::class,
                RealisasiKinerja::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                // \App\Filament\Widgets\AnggaranKasOverviewWidget::class,
                // \App\Filament\Widgets\AnggaranKasChartWidget::class,
                YearSelectorWidget::class,
                GreetingWidget::class,
                RencanaAnggaranKasWidget::class,
                RiwayatAnggaranKasWidget::class,
            ])
            // ->viteTheme('resources/css/filament/admin/theme.css')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('Master Data')
                    ->label('Master Data')
                    ->icon('heroicon-o-circle-stack')
                    ->collapsible(),
                NavigationGroup::make('Manajemen Anggaran')
                    ->label('Manajemen Anggaran')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->collapsible(),
                NavigationGroup::make('Perencanaan')
                    ->label('Perencanaan')
                    ->icon('heroicon-o-chart-bar')
                    ->collapsible(),
                NavigationGroup::make('Capaian Kinerja')
                    ->label('Capaian Kinerja')
                    ->icon('heroicon-o-chart-pie')
                    ->collapsible(),
                NavigationGroup::make('Pengguna dan SOTK')
                    ->label('Pengguna dan SOTK')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible(),
            ])
            ->sidebarCollapsibleOnDesktop()
            // ->sidebarCollapsed(true)
            // ->spa()
            ->sidebarFullyCollapsibleOnDesktop(false)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            // ->databaseNotifications()
            // ->databaseNotificationsPolling('30s')
            ->spa()
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
                // OwnerUserVerifiedMiddleware::class,
            ]);
        // ->resources([
        //     ProgramResource::class,
        // ]);
    }
}
