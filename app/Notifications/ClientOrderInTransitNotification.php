<?php
namespace App\Notifications;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientOrderInTransitNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public Order $order;

    public function __construct(Order $order) { $this->order = $order; }
    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject("Votre commande SOMACIF N°{$this->order->numero_commande} est en route !")
                    ->greeting("Bonjour {$notifiable->nom},")
                    ->line("Bonne nouvelle ! Votre commande **{$this->order->numero_commande}** a été récupérée et est actuellement en cours de livraison.")
                    ->line("Votre livreur, **{$this->order->livreur->full_name}**, devrait arriver prochainement.")
                    ->line("Vous pouvez le contacter si besoin au : **{$this->order->livreur->telephone}**")
                    ->action('Voir ma commande', route('client.orders.show', $this->order))
                    ->line('Merci de votre confiance.');
    }
}