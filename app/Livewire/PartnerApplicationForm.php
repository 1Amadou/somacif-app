<?php

namespace App\Livewire;

use App\Models\PartnerApplication;
use App\Models\User;
use App\Notifications\NewPartnerRequestNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule; // <-- AJOUT 1 : Importer la classe Rule

class PartnerApplicationForm extends Component
{
    public $company_name = '';
    public $company_type = '';
    public $contact_name = '';
    public $phone = '';
    public $email = '';
    public $message = '';

    public $applicationSubmitted = false;
    public $generatedId = '';
    public bool $isClientLoggedIn = false;

    public function mount()
    {
        $this->isClientLoggedIn = Auth::guard('client')->check();
    }

    protected function rules()
    {
        return [
            'company_name' => 'required|string|max:255',
            'company_type' => 'required|string',
            'contact_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            // --- AMÉLIORATION DE LA VALIDATION ---
            'email' => [
                'required',
                'email',
                'max:255',
                // La règle ignore() est incorrecte ici, on utilise unique()
                Rule::unique('clients', 'email'), // Vérifie que l'email n'existe pas dans la table 'clients'
            ],
            'message' => 'nullable|string',
        ];
    }

    // On personnalise le message d'erreur pour la règle 'unique'
    protected $messages = [
        'email.unique' => 'Cette adresse e-mail est déjà utilisée par un partenaire. Veuillez vous connecter.',
    ];
    
    public function submit()
    {
        $data = $this->validate();
        
        $this->generatedId = 'SOMACIF-' . Str::upper(Str::random(6));
        
        $applicationData = [
            'nom_entreprise' => $data['company_name'],
            'secteur_activite' => $data['company_type'],
            'nom_contact' => $data['contact_name'],
            'telephone' => $data['phone'],
            'email' => $data['email'],
            'message' => $data['message'],
            'identifiant_temporaire' => $this->generatedId,
        ];
        
        $application = PartnerApplication::create($applicationData);

        $admins = User::where('is_admin', true)->get();
        // La notification attend maintenant un objet PartnerApplication, donc c'est correct
        foreach ($admins as $admin) {
            $admin->notify(new NewPartnerRequestNotification($application));
        }
        
        $this->applicationSubmitted = true;
    }

    public function render()
    {
        return view('livewire.partner-application-form');
    }
}