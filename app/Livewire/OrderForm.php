<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;
use Twilio\Rest\Client as TwilioClient;

class OrderForm extends Component
{
    public int $step = 1;
    public string $identifiant_unique = '';
    public string $verification_code = '';
    public ?Client $client = null;
    public string $message = '';

    public function checkIdentifiant()
    {
        $this->validate(['identifiant_unique' => 'required|string']);
        $this->message = '';
        $client = Client::where('identifiant_unique_somacif', $this->identifiant_unique)->first();

        if ($client) {
            $this->client = $client;
            $code = random_int(100000, 999999);
            session(['verification_code' => $code, 'client_id_to_verify' => $client->id]);

            // Gestion du mode Bac à Sable
            if (config('settings.twilio_sandbox_mode', true)) {
                $this->message = "Mode BAC À SABLE : Votre code est {$code}";
                $this->step = 2;
                return;
            }

            // Logique d'envoi de SMS réelle
            try {
                $twilioSid = config('services.twilio.sid');
                $twilioToken = config('services.twilio.token');
                $twilioFrom = config('services.twilio.from');

                if ($twilioSid && $twilioToken && $twilioFrom) {
                    $twilio = new TwilioClient($twilioSid, $twilioToken);
                    $twilio->messages->create(
                        $this->client->telephone,
                        ['from' => $twilioFrom, 'body' => "Votre code de vérification SOMACIF est : {$code}"]
                    );
                    $this->message = "Un code a été envoyé au numéro associé à {$this->client->nom}.";
                    $this->step = 2;
                } else {
                    $this->message = "Erreur de configuration : paramètres SMS manquants. Mode DÉVELOPPEMENT : Votre code est {$code}";
                    $this->step = 2;
                }
            } catch (\Exception $e) {
                logger()->error('Erreur Twilio: ' . $e->getMessage());
                $this->message = "Erreur d'envoi SMS. Mode DÉVELOPPEMENT : Votre code est {$code}";
                $this->step = 2;
            }
        } else {
            $this->message = "Cet identifiant est inconnu. Veuillez vérifier ou nous contacter.";
        }
    }

    public function verifyCode()
    {
        $this->validate(['verification_code' => 'required|numeric|digits:6']);
        $this->message = '';
        $storedCode = session('verification_code');

        if ($this->verification_code == $storedCode) {
            session(['authenticated_client_id' => $this->client->id]);
            session()->forget(['verification_code', 'client_id_to_verify']);
            $this->dispatch('clientAuthenticated', clientId: $this->client->id);

            switch ($this->client->status) {
                case 'approved':
                    return $this->redirect(route('products.index'), navigate: true);
                case 'pending':
                    $this->step = 4;
                    break;
                case 'rejected':
                    $this->step = 5;
                    break;
            }
        } else {
            $this->message = "Le code de vérification est incorrect.";
        }
    }

    public function logout()
    {
        session()->forget('authenticated_client_id');
        $this->reset();
        return $this->redirect(route('home'), navigate: true);
    }
    
    public function render()
    {
        return view('livewire.order-form');
    }
}