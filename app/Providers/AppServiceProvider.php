<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route; 
use App\Models\Client;
use App\Models\Page;
use App\Models\Arrivage;
use App\Observers\ArrivageObserver;
use App\Models\Order;
use App\Observers\OrderObserver; 
use App\Models\OrderItem;
use App\Observers\OrderItemObserver;
use App\Models\Reglement; 
use App\Observers\ReglementObserver; 


class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // On appelle la méthode pour enregistrer nos routes
        $this->bootRoutes();

        try {
            if (Schema::hasTable('pages')) {
                View::composer('*', function ($view) {
                    $authenticatedClient = session()->has('authenticated_client_id') 
                        ? Client::find(session('authenticated_client_id')) 
                        : null;
                    
                    $siteHeader = cache()->remember('site_header', 3600, fn() => Page::where('slug', '_header')->first());
                    $siteFooter = cache()->remember('site_footer', 3600, fn() => Page::where('slug', '_footer')->first());

                    $view->with([
                        'authenticatedClient' => $authenticatedClient,
                        'siteHeader' => $siteHeader,
                        'siteFooter' => $siteFooter,
                    ]);
                });
            }
        } catch (\Exception $e) {
            return;
        }

        Arrivage::observe(ArrivageObserver::class);
        Order::observe(OrderObserver::class);
        OrderItem::observe(OrderItemObserver::class);
        Reglement::observe(ReglementObserver::class);
        
    }

    // ON AJOUTE CETTE MÉTHODE POUR CHARGER NOS FICHIERS DE ROUTES
    protected function bootRoutes(): void
    {
        Route::middleware('web')
            ->prefix('livreur')
            ->name('livreur.')
            ->group(base_path('routes/livreur.php'));
    }
}