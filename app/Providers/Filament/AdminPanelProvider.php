<?php

namespace App\Providers\Filament;

use App\Filament\Pages\PerencanaanKinerja;
use App\Filament\Pages\RealisasiKinerja;
use App\Filament\Resources\ProgramResource;
use App\Filament\Widgets\ProgramCategoryChart;
use App\Filament\Widgets\ProgramOverviewWidget;
use App\Filament\Widgets\RecentProgramsWidget;
use App\Filament\Widgets\YearSelectorHeaderWidget;
use App\Http\Middleware\OwnerUserVerifiedMiddleware;
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
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
                // YearSelectorHeaderWidget::class,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
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
                NavigationGroup::make('Perencanaan')
                    ->label('Perencanaan')
                    ->icon('heroicon-o-chart-bar')
                    ->collapsible(),
                NavigationGroup::make('Capaian Kinerja')
                    ->label('Capaian Kinerja')
                    ->icon('heroicon-o-chart-pie')
                    ->collapsible(),
                NavigationGroup::make('Users Management')
                    ->label('Users Management')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible(),
            ])
            ->sidebarFullyCollapsibleOnDesktop()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            // ->databaseNotifications()
            // ->databaseNotificationsPolling('30s')
            ->spa()
            ->plugins([
                FilamentShieldPlugin::make(),
            ])->authMiddleware([
                Authenticate::class,
                // OwnerUserVerifiedMiddleware::class,
            ]);
        // ->resources([
        //     ProgramResource::class,
        // ]);
    }
}
