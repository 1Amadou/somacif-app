<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // On vérifie que la table settings existe pour éviter les erreurs lors des premières migrations
        if (Schema::hasTable('settings')) {
            $settings = Setting::all();

            foreach ($settings as $setting) {
                Config::set('settings.' . $setting->key, $setting->value);
            }

            // On configure les services dynamiquement
            if (Config::has('settings.twilio_sid')) {
                Config::set('services.twilio.sid', Config::get('settings.twilio_sid'));
                Config::set('services.twilio.token', Config::get('settings.twilio_auth_token'));
                Config::set('services.twilio.from', Config::get('settings.twilio_from'));
            }

            if (Config::has('settings.mail_host')) {
                Config::set('mail.mailers.smtp.host', Config::get('settings.mail_host'));
                Config::set('mail.mailers.smtp.port', Config::get('settings.mail_port'));
                Config::set('mail.mailers.smtp.username', Config::get('settings.mail_username'));
                Config::set('mail.mailers.smtp.password', Config::get('settings.mail_password'));
                Config::set('mail.from.address', Config::get('settings.mail_from_address'));
                Config::set('mail.from.name', Config::get('settings.mail_from_name'));
            }
        }
    }
}