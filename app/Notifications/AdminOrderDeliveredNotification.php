<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminOrderDeliveredNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Confirmation : Commande livrée')
                    ->line('La commande #' . $this->order->numero_commande . ' a bien été marquée comme livrée.')
                    ->line('Confirmée par le client le : ' . $this->order->client_confirmed_at->format('d/m/Y à H:i'))
                    ->line('Le déstockage final a été effectué.')
                    ->action('Voir la commande', url('/admin/orders/' . $this->order->id));
    }
}