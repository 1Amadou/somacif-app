<?php

namespace App\Notifications;

use App\Notifications\Channels\TwilioChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SmsNotification extends Notification
{
    use Queueable;
    public string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        // On utilise notre nouveau canal personnalisé
        return [TwilioChannel::class];
    }

    public function toSms(object $notifiable): string
    {
        return $this->message;
    }
}