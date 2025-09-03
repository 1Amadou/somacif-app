<?php

namespace App\Providers;

use App\Models\Arrivage;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Page;
use App\Models\Reglement;
use App\Models\Setting;
use App\Notifications\QueueJobFailedNotification;
use App\Observers\ArrivageObserver;
use App\Observers\OrderItemObserver;
use App\Observers\OrderObserver;
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
            // S'assure que la base de données est prête avant de continuer
            if (Schema::hasTable('settings') && Schema::hasTable('pages')) {
                // Charge les paramètres SMTP dynamiques
                $this->bootMailConfiguration();
                // Partage les données avec toutes les vues du site
                $this->bootViewComposers();
                // Met en place les alertes pour les tâches échouées
                $this->bootQueueAlerts();
            }
        } catch (\Exception $e) {
            // En cas d'erreur (ex: pendant les migrations), on continue sans planter.
            return;
        }
    }

    /**
     * Enregistre les observers de l'application.
     */
    private function bootObservers(): void
    {
        Arrivage::observe(ArrivageObserver::class);
        Order::observe(OrderObserver::class);
        OrderItem::observe(OrderItemObserver::class);
        Reglement::observe(ReglementObserver::class);
        VenteDirecte::observe(VenteDirecteObserver::class);
    }

    /**
     * Partage les données globales avec toutes les vues Blade.
     */
    private function bootViewComposers(): void
    {
        View::composer('*', function ($view) {
            // CORRECTION : Utilise le système d'authentification standard de Laravel
            $authenticatedClient = Auth::guard('client')->user();
            
            $siteHeader = cache()->remember('site_header', 3600, fn() => Page::where('slug', '_header')->first());
            $siteFooter = cache()->remember('site_footer', 3600, fn() => Page::where('slug', '_footer')->first());

            $view->with(compact('authenticatedClient', 'siteHeader', 'siteFooter'));
        });
    }

    /**
     * Charge la configuration SMTP depuis la base de données.
     */
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

    /**
     * Configure les alertes en temps réel pour les tâches échouées.
     */
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