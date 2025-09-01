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
    // Définition des propriétés qui seront liées au formulaire
    public $company_name = '';
    public $company_type = ''; // Ajout de cette propriété
    public $contact_name = '';
    public $phone = '';
    public $email = ''; // Ajout de cette propriété
    public $message = '';

    // Propriétés pour la gestion de l'état de la vue
    public $applicationSubmitted = false;
    public $generatedId = '';

    protected function rules()
    {
        return [
            'company_name' => 'required|string|max:255',
            'company_type' => 'required|string', // Règle de validation pour le nouveau champ
            'contact_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'message' => 'nullable|string',
        ];
    }
    
    // Méthode de soumission du formulaire
    public function submit()
    {
        $data = $this->validate();
        
        // Générer un identifiant unique
        $this->generatedId = 'SOMACIF-' . Str::upper(Str::random(6));
        $data['temp_id'] = $this->generatedId;
        
        $application = PartnerApplication::create($data);

        // Notifier l'administrateur
        $admin = User::first(); // Assurez-vous qu'un admin existe
        if ($admin) {
            $admin->notify(new NewPartnerRequestNotification($application));
        }
        
        // Mettre à jour la variable pour afficher le message de succès
        $this->applicationSubmitted = true;
        
        // Réinitialiser les champs du formulaire après la soumission (si nécessaire)
        // Note: La réinitialisation est gérée par le basculement de l'affichage.
    }

    public function render()
    {
        return view('livewire.partner-application-form');
    }
}