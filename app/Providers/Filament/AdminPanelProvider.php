<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AdminOverviewStats;
use App\Filament\Widgets\BloodGroupInventoryChart;
use App\Filament\Widgets\CampaignOverviewStats;
use App\Filament\Widgets\DashboardHeroWidget;
use App\Filament\Widgets\DonationTrendChart;
use App\Filament\Widgets\InventoryOverviewStats;
use App\Filament\Widgets\LoyaltyOverviewStats;
use App\Filament\Widgets\OperationsOverviewStats;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\HtmlString;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(isSimple: false)
            ->brandName('NBTS')
            ->brandLogo(fn () => view('filament.admin.brand'))
            ->brandLogoHeight('2.75rem')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(MaxWidth::Full)
            ->defaultThemeMode(ThemeMode::Light)
            ->colors([
                'primary' => Color::Rose,
                'danger' => Color::Red,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'info' => Color::Sky,
            ])
            ->navigationGroups([
                NavigationGroup::make('Operations')
                    ->icon('heroicon-o-calendar-days')
                    ->collapsible(false),
                NavigationGroup::make('Inventory')
                    ->icon('heroicon-o-circle-stack')
                    ->collapsible(false),
                NavigationGroup::make('Management')
                    ->icon('heroicon-o-users')
                    ->collapsible(false),
                NavigationGroup::make('Donor Safety')
                    ->icon('heroicon-o-shield-check'),
                NavigationGroup::make('Loyalty')
                    ->icon('heroicon-o-gift')
                    ->collapsed(),
                NavigationGroup::make('Access Control')
                    ->icon('heroicon-o-key')
                    ->collapsed(),
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('Account settings')
                    ->icon('heroicon-o-cog-6-tooth'),
                'logout' => MenuItem::make()
                    ->label('Sign out')
                    ->icon('heroicon-o-arrow-right-start-on-rectangle'),
            ])
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): HtmlString => new HtmlString('<link rel="stylesheet" href="' . asset('css/filament/admin/account.css') . '">')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                DashboardHeroWidget::class,
                AdminOverviewStats::class,
                OperationsOverviewStats::class,
                InventoryOverviewStats::class,
                CampaignOverviewStats::class,
                LoyaltyOverviewStats::class,
                DonationTrendChart::class,
                BloodGroupInventoryChart::class,
            ])
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
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
