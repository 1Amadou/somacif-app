<?php

namespace App\Notifications;

use App\Models\ContactMessage; // <-- AJOUT 1 : Importer notre modèle
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContactMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // --- CORRECTION : On attend un objet ContactMessage, pas un tableau ---
    public ContactMessage $message;

    public function __construct(ContactMessage $message)
    {
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // --- CORRECTION : On utilise les propriétés de l'objet ---
        return (new MailMessage)
                    ->subject("Nouveau Message de Contact : {$this->message->subject}")
                    ->greeting('Bonjour,')
                    ->line("Vous avez reçu un nouveau message via le formulaire de contact de votre site web.")
                    ->line('**Nom :** ' . $this->message->name)
                    ->line('**Email :** ' . $this->message->email)
                    ->line('**Téléphone :** ' . ($this->message->phone ?? 'Non fourni'))
                    ->line('**Sujet :** ' . $this->message->subject)
                    ->line('**Message :**')
                    ->line($this->message->message)
                    ->action('Voir le message dans l\'admin', url('/admin/contact-messages/' . $this->message->id))
                    ->line('Merci.');
    }
}