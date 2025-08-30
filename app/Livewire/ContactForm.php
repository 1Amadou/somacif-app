<?php

namespace App\Livewire;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\NewContactMessageNotification;
use Livewire\Component;

class ContactForm extends Component
{
    public $nom = '';
    public $email = '';
    public $sujet = '';
    public $message = '';
    public $isSent = false;

    protected $rules = [
        'nom' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'sujet' => 'required|string|max:255',
        'message' => 'required|string|min:10',
    ];

    public function submit()
    {
        $data = $this->validate();

        // Notifier l'administrateur
        // On récupère l'email de notification depuis les paramètres
        $adminEmail = Setting::where('key', 'admin_notification_email')->value('value');
        
        if ($adminEmail) {
            // On envoie la notification à l'adresse configurée
            (new User(['email' => $adminEmail]))->notify(new NewContactMessageNotification($data));
        }

        session()->flash('success', 'Merci pour votre message ! Nous vous répondrons dans les plus brefs délais.');
        $this->reset();
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}