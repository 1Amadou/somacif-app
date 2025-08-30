<?php

namespace App\Livewire;

use App\Models\PartnerApplication;
use App\Models\User;
use App\Notifications\NewPartnerRequestNotification;
use Filament\Notifications\Notification as FilamentNotification;
use Livewire\Component;

class PartnerApplicationForm extends Component
{
    public $nom_entreprise = '';
    public $nom_contact = '';
    public $telephone = '';
    public $email = '';
    public $secteur_activite = '';
    public $message = '';

    protected function rules()
    {
        return [
            'nom_entreprise' => 'required|string|max:255',
            'nom_contact' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'secteur_activite' => 'required|string',
            'message' => 'nullable|string',
        ];
    }

    public function submit()
    {
        $data = $this->validate();
        $application = PartnerApplication::create($data);

        // Notifier l'administrateur
        $admin = User::first(); // Assurez-vous qu'un admin existe
        if ($admin) {
            $admin->notify(new NewPartnerRequestNotification($application));
        }
        
        session()->flash('success', 'Merci ! Votre demande a bien été envoyée. Nous vous contacterons prochainement.');
        $this->reset();
    }

    public function render()
    {
        return view('livewire.partner-application-form');
    }
}