<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientDeliveryInProgressNotification extends Notification
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
        $livreur = $this->order->livreur;

        return (new MailMessage)
                    ->subject('Votre commande est en route !')
                    ->line('Bonne nouvelle ! Votre commande #' . $this->order->numero_commande . ' est en cours de livraison.')
                    ->line('Votre livreur, ' . $livreur->fullName . ', est en chemin.')
                    ->line('Vous pouvez le contacter si besoin au : ' . $livreur->telephone)
                    ->action('Suivre ma commande', route('client.orders.show', $this->order));
    }
}