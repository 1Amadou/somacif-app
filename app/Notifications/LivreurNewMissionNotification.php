<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LivreurNewMissionNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via($notifiable): array
    {
        return ['mail']; // Peut aussi être ['mail', 'database'] ou un canal SMS
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Nouvelle mission de livraison')
                    ->line('Une nouvelle mission de livraison vous a été assignée.')
                    ->line('Commande #: ' . $this->order->numero_commande)
                    ->action('Voir la mission', route('livreur.orders.show', $this->order))
                    ->line('Merci d\'accepter la mission depuis votre tableau de bord.');
    }
}