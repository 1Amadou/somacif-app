<?php

namespace App\Livewire;

use App\Models\PartnerApplication;
use App\Models\User;
use App\Notifications\NewPartnerRequestNotification;
use Filament\Notifications\Notification as FilamentNotification;
use Livewire\Component;
use Illuminate\Support\Str;

class PartnerApplicationForm extends Component
{
    public $nom_entreprise = ''; // Renommé pour correspondre au modèle
    public $secteur_activite = ''; // Renommé pour correspondre au modèle
    public $nom_contact = ''; // Renommé pour correspondre au modèle
    public $telephone = ''; // Renommé pour correspondre au modèle
    public $email = ''; // Renommé pour correspondre au modèle
    public $message = '';

    public $applicationSubmitted = false;
    public $generatedId = '';

    protected function rules()
    {
        return [
            'nom_entreprise' => 'required|string|max:255',
            'secteur_activite' => 'required|string',
            'nom_contact' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'message' => 'nullable|string',
        ];
    }
    
    public function submit()
    {
        $data = $this->validate();
        
        // Générer un identifiant unique pour le suivi
        $this->generatedId = 'SOMACIF-' . Str::upper(Str::random(6));
        
        // Ajouter l'identifiant à la logique de création
        $data['identifiant_temporaire'] = $this->generatedId;
        
        $application = PartnerApplication::create($data);

        // Notifier les administrateurs (plus fiable que de prendre le premier utilisateur)
        $admins = User::where('is_admin', true)->get(); // Ou un rôle spécifique
        foreach ($admins as $admin) {
            $admin->notify(new NewPartnerRequestNotification($application));
        }
        
        // Afficher la page de succès
        $this->applicationSubmitted = true;
    }

    public function render()
    {
        return view('livewire.partner-application-form');
    }
}