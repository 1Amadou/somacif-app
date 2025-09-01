<?php
namespace App\Notifications;
// ... (use statements)
class QueueJobFailedNotification extends Notification
{
    public JobFailed $event;

    public function __construct(JobFailed $event)
    {
        $this->event = $event;
    }
    // ... (méthodes via() et toMail())
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->error() // Pour un style d'alerte
                    ->subject('Alerte : Une tâche a échoué sur SOMACIF')
                    ->greeting('Attention, une tâche automatisée a échoué.')
                    ->line('**Tâche :** ' . $this->event->job->resolveName())
                    ->line('**Erreur :** ' . $this->event->exception->getMessage());
    }
}