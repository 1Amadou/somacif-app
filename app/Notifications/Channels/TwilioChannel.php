<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client as TwilioClient;

class TwilioChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toSms')) {
            return;
        }

        $message = $notification->toSms($notifiable);

        if (config('settings.sms_sandbox_mode', true)) {
            // En mode bac à sable, on n'envoie rien, on logue simplement le message
            logger()->info("SMS (Sandbox) to {$notifiable->telephone}: {$message}");
            return;
        }

        $twilioSid = config('settings.twilio_sid');
        $twilioToken = config('settings.twilio_auth_token');
        $twilioFrom = config('settings.twilio_from');

        if (!$twilioSid || !$twilioToken || !$twilioFrom) {
            logger()->error('Twilio settings are not configured.');
            return;
        }

        try {
            $twilio = new TwilioClient($twilioSid, $twilioToken);
            $twilio->messages->create(
                $notifiable->telephone,
                [
                    'from' => $twilioFrom,
                    'body' => $message
                ]
            );
        } catch (\Exception $e) {
            logger()->error('Twilio SMS sending failed: ' . $e->getMessage());
        }
    }
}