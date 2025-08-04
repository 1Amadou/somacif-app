<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Client;
use App\Models\Page; // Ajoutez cette ligne

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function ($view) {
            // Client authentifié (déjà présent)
            $authenticatedClient = session()->has('authenticated_client_id') 
                ? Client::find(session('authenticated_client_id')) 
                : null;
            
            // On charge les données du header et du footer une seule fois pour tout le site
            $siteHeader = Page::where('slug', '_header')->first();
            $siteFooter = Page::where('slug', '_footer')->first();

            $view->with([
                'authenticatedClient' => $authenticatedClient,
                'siteHeader' => $siteHeader,
                'siteFooter' => $siteFooter,
            ]);
        });
    }
}