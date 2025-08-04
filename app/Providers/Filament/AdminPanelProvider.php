<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard; // Assurez-vous que cette ligne est présente
use App\Models\Page;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Logique pour charger le logo dynamiquement
        $headerSettings = Page::where('slug', '_header')->first();
        $logoUrl = $headerSettings && !empty($headerSettings->images['logo'])
            ? Storage::url($headerSettings->images['logo'])
            : null;

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandLogo($logoUrl) // Logo dynamique
            ->brandName('SOMACIF') // Fallback si pas de logo
            ->brandLogoHeight('3rem')
            ->favicon(asset('favicon.svg'))
            ->colors([
                'primary' => '#D32F2F',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class, // On enregistre notre page de tableau de bord
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Les widgets sont maintenant gérés par la page Dashboard.php,
                // on peut laisser ce tableau vide.
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