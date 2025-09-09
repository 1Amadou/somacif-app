<?php

namespace App\Notifications;

use App\Models\PartnerApplication; 
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPartnerRequestNotification extends Notification
{
    use Queueable;

    // --- CORRECTION 2 : On attend un objet PartnerApplication ---
    public function __construct(public PartnerApplication $application) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // --- CORRECTION 3 : On utilise les propriétés du bon objet ---
        return (new MailMessage)
                    ->subject('Nouvelle Demande de Partenariat : ' . $this->application->nom_entreprise)
                    ->greeting('Bonjour,')
                    ->line('Une nouvelle demande de partenariat a été soumise sur le site web.')
                    ->line('**Nom de l\'entreprise :** ' . $this->application->nom_entreprise)
                    ->line('**Contact :** ' . $this->application->nom_contact)
                    ->line('**Téléphone :** ' . $this->application->telephone)
                    ->action('Voir la demande dans l\'admin', url('/admin/partner-applications/' . $this->application->id));
    }
}