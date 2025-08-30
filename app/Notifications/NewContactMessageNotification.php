<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContactMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public array $data) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject("Nouveau Message de Contact : {$this->data['sujet']}")
                    ->greeting('Bonjour,')
                    ->line("Vous avez reçu un nouveau message depuis le formulaire de contact du site.")
                    ->line("**Nom :** {$this->data['nom']}")
                    ->line("**Email :** {$this->data['email']}")
                    ->line("**Sujet :** {$this->data['sujet']}")
                    ->line('---')
                    ->line($this->data['message']);
    }
}