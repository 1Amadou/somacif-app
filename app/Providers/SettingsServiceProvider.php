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
        if (Schema::hasTable('settings')) {
            $settings = Setting::all();

            foreach ($settings as $setting) {
                Config::set('settings.' . $setting->key, $setting->value);
            }

            if (Config::has('settings.mail_host')) {
                Config::set('mail.mailers.smtp.host', Config::get('settings.mail_host'));
                Config::set('mail.mailers.smtp.port', Config::get('settings.mail_port'));
                Config::set('mail.mailers.smtp.username', Config::get('settings.mail_username'));
                Config::set('mail.mailers.smtp.password', base64_decode(Config::get('settings.mail_password')));
                Config::set('mail.mailers.smtp.encryption', Config::get('settings.mail_encryption')); // Ajouté
                Config::set('mail.from.address', Config::get('settings.mail_from_address'));
                Config::set('mail.from.name', Config::get('settings.mail_from_name'));
            }

            if (Config::get('settings.active_sms_provider') === 'twilio') {
                Config::set('services.twilio.sid', Config::get('settings.twilio_sid'));
                Config::set('services.twilio.token', base64_decode(Config::get('settings.twilio_auth_token')));
                Config::set('services.twilio.from', Config::get('settings.twilio_from'));
            } elseif (Config::get('settings.active_sms_provider') === 'nexmo') {
                Config::set('services.nexmo.key', Config::get('settings.nexmo_key'));
                Config::set('services.nexmo.secret', base64_decode(Config::get('settings.nexmo_secret')));
            } elseif (Config::get('settings.active_sms_provider') === 'fast2') {
                Config::set('services.fast2.sender_id', Config::get('settings.fast2_sender_id'));
                Config::set('services.fast2.auth_key', base64_decode(Config::get('settings.fast2_auth_key')));
            }
        }
    }
}