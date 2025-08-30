<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MagicLoginCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Votre code de connexion SOMACIF')
                    ->greeting('Bonjour !')
                    ->line('Voici votre code de connexion à usage unique pour accéder à votre espace SOMACIF.')
                    ->line("Code : **{$this->code}**")
                    ->line('Ce code expirera dans 10 minutes.')
                    ->line('Si vous n\'avez pas demandé ce code, vous pouvez ignorer cet email en toute sécurité.');
    }
}