<?php
namespace App\Notifications;
use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class NewPartnerRequestNotification extends Notification
{
    use Queueable;
    public function __construct(public Client $client) {}
    public function via(object $notifiable): array { return ['mail']; }
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Nouvelle Demande de Partenariat : ' . $this->client->nom)
                    ->greeting('Bonjour,')
                    ->line('Une nouvelle demande de partenariat a été soumise.')
                    ->line('Nom de l\'entreprise : ' . $this->client->nom)
                    ->line('Téléphone : ' . $this->client->telephone)
                    ->action('Voir la demande', url('/admin/clients/' . $this->client->id . '/edit'));
    }
}