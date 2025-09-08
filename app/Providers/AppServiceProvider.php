<?php

namespace App\Providers;

use App\Models\Arrivage;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Page;
use App\Models\PointDeVente; // Ajout
use App\Models\Reglement;
use App\Models\Setting;
use App\Notifications\QueueJobFailedNotification;
use App\Observers\ArrivageObserver;
use App\Observers\OrderItemObserver;
use App\Observers\OrderObserver;
use App\Observers\PointDeVenteObserver; // Ajout
use App\Observers\ReglementObserver;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification as Notifier;
use App\Models\VenteDirecte;
use App\Observers\VenteDirecteObserver;
use App\Models\StockTransfert;
use App\Observers\StockTransfertObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // On enregistre nos observers
        $this->bootObservers();
        
        try {
            if (Schema::hasTable('settings') && Schema::hasTable('pages')) {
                $this->bootMailConfiguration();
                $this->bootViewComposers();
                $this->bootQueueAlerts();
            }
        } catch (\Exception $e) {
            return;
        }
    }

    private function bootObservers(): void
    {
        Arrivage::observe(ArrivageObserver::class);
        Order::observe(OrderObserver::class);
        OrderItem::observe(OrderItemObserver::class); // Souvent pas nécessaire si OrderObserver gère tout
        Reglement::observe(ReglementObserver::class);
        VenteDirecte::observe(VenteDirecteObserver::class);
        StockTransfert::observe(StockTransfertObserver::class);
        
        // *** CORRECTION : Ajout de l'observateur manquant ***
        // C'est lui qui crée le lieu de stockage pour chaque nouveau point de vente.
        PointDeVente::observe(PointDeVenteObserver::class);
    }

    private function bootViewComposers(): void
    {
        View::composer('*', function ($view) {
            $authenticatedClient = Auth::guard('client')->user();
            $siteHeader = cache()->remember('site_header', 3600, fn() => Page::where('slug', '_header')->first());
            $siteFooter = cache()->remember('site_footer', 3600, fn() => Page::where('slug', '_footer')->first());
            $view->with(compact('authenticatedClient', 'siteHeader', 'siteFooter'));
        });
    }

    private function bootMailConfiguration(): void
    {
        $settings = Setting::where('group', 'smtp')->pluck('value', 'key');
        if ($settings->isNotEmpty()) {
            Config::set('mail.mailers.smtp.host', $settings->get('mail_host'));
            Config::set('mail.mailers.smtp.port', $settings->get('mail_port'));
            Config::set('mail.mailers.smtp.encryption', $settings->get('mail_encryption'));
            Config::set('mail.mailers.smtp.username', $settings->get('mail_username'));
            Config::set('mail.mailers.smtp.password', $settings->get('mail_password'));
            Config::set('mail.from.address', $settings->get('mail_from_address'));
            Config::set('mail.from.name', $settings->get('mail_from_name'));
        }
    }

    private function bootQueueAlerts(): void
    {
        Queue::failing(function (JobFailed $event) {
            $adminEmail = Setting::where('key', 'admin_notification_email')->value('value');
            if ($adminEmail) {
                Notifier::route('mail', $adminEmail)
                    ->notify(new QueueJobFailedNotification($event));
            }
        });
    }
}