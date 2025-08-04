<?php
namespace App\Notifications;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class NewOrderAdminNotification extends Notification
{
    use Queueable;
    public function __construct(public Order $order) {}
    public function via(object $notifiable): array { return ['mail']; }
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Nouvelle Commande Reçue : ' . $this->order->numero_commande)
                    ->greeting('Bonjour,')
                    ->line('Une nouvelle commande a été passée sur le site SOMACIF.')
                    ->line('Client : ' . $this->order->client->nom)
                    ->line('Montant Total : ' . number_format($this->order->montant_total, 0, ',', ' ') . ' FCFA')
                    ->action('Voir la commande', url('/admin/orders/' . $this->order->id));
    }
}