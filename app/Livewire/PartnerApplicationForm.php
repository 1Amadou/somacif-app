<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;
use Illuminate\Support\Facades\Notification as Notifier;
use App\Notifications\NewPartnerRequestNotification;
use Illuminate\Support\Str;

class PartnerApplicationForm extends Component
{
    public string $company_name = '';
    public string $company_type = 'Hôtel / Restaurant';
    public string $contact_name = '';
    public string $phone = '';
    public string $message = '';

    public bool $applicationSubmitted = false;
    public string $generatedId = '';

    protected function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'company_type' => 'required|string',
            'contact_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:clients,telephone',
            'message' => 'required|string|min:20',
        ];
    }

    public function submit()
    {
        $this->validate();

        // Génération d'un identifiant temporaire
        $this->generatedId = 'TEMP-' . Str::upper(Str::random(8));

        // Création du client avec le statut "en attente"
        $client = Client::create([
            'nom' => $this->company_name,
            'type' => $this->company_type,
            'status' => 'pending',
            'telephone' => $this->phone,
            'identifiant_unique_somacif' => $this->generatedId,
            'email' => null, // L'admin le complètera
        ]);

        // Envoi d'une notification à l'administrateur
        $adminEmail = config('settings.admin_notification_email');
        if ($adminEmail) {
            Notifier::route('mail', $adminEmail)->notify(new NewPartnerRequestNotification($client));
        }

        $this->applicationSubmitted = true;
    }

    public function render()
    {
        return view('livewire.partner-application-form');
    }
}