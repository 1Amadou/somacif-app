<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\NotificationTemplate;
use App\Notifications\NewPartnerRequestNotification;
use Illuminate\Support\Facades\Notification as Notifier;
use Livewire\Component;

class PartnerApplicationForm extends Component
{
    public string $company_name = '';
    public string $company_type = 'Hôtel/Restaurant';
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
        $this->generatedId = 'TEMP-' . time();

        $client = Client::create([
            'nom' => $this->company_name,
            'type' => $this->company_type,
            'status' => 'pending',
            'telephone' => $this->phone,
            'identifiant_unique_somacif' => $this->generatedId,
        ]);

        // On notifie l'admin en utilisant le système dynamique
        $adminEmail = config('settings.admin_notification_email');
        $template = NotificationTemplate::where('key', 'admin.new_partner_request')->first();
        if ($adminEmail && $template && $template->is_active && config('settings.mail_notifications_active')) {
            Notifier::route('mail', $adminEmail)->notify(new NewPartnerRequestNotification($client));
        }

        $this->applicationSubmitted = true;
    }

    public function render()
    {
        return view('livewire.partner-application-form');
    }
}