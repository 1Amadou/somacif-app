<?php

namespace App\Notifications;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PartnerApprovedNotification extends Notification
{
    use Queueable;

    public Client $client;
    public string $password;

    public function __construct(Client $client, string $password)
    {
        $this->client = $client;
        $this->password = $password;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Votre demande de partenariat SOMACIF a été approuvée !')
                    ->greeting('Bonjour ' . $this->client->nom . ',')
                    ->line('Félicitations ! Votre compte partenaire a été créé avec succès.')
                    ->line('Voici vos informations pour vous connecter à votre espace client :')
                    ->line('**Identifiant :** ' . $this->client->identifiant_unique_somacif)
                    ->line('**Mot de passe temporaire :** ' . $this->password)
                    
                    ->action('Accéder à mon espace', route('login'))
                    ->line('Merci de nous faire confiance.');
    }
}