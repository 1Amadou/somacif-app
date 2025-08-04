<?php

namespace App\Livewire;

use App\Models\Client;
use Livewire\Component;
use Twilio\Rest\Client as TwilioClient;
use Illuminate\Support\Facades\Log;

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

            // ON VÉRIFIE LE MODE BAC À SABLE
            if (config('settings.twilio_sandbox_mode', true)) {
                $this->message = "Mode BAC À SABLE : Votre code est {$code}";
                $this->step = 2;
                return;
            }

            try {
                // On utilise la configuration de Laravel pour Twilio
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
                    // Si les paramètres Twilio ne sont pas configurés
                    Log::warning("Paramètres Twilio non configurés. Envoi de SMS en mode développement pour le client ID: {$client->id}");
                    $this->message = "Mode DÉVELOPPEMENT (paramètres SMS non configurés) : Votre code est {$code}";
                    $this->step = 2;
                }
            } catch (\Exception $e) {
                Log::error('Erreur Twilio: ' . $e->getMessage(), ['client_id' => $client->id, 'phone' => $client->telephone]);
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
        $clientIdToVerify = session('client_id_to_verify');

        // On s'assure que le client et le code existent bien en session
        if ($storedCode && $clientIdToVerify && $this->verification_code == $storedCode) {
            $this->client = Client::find($clientIdToVerify);
            if ($this->client) {
                session(['authenticated_client_id' => $this->client->id]);
                session()->forget(['verification_code', 'client_id_to_verify']);

                // ON ENREGISTRE LA CONNEXION ICI
                $this->client->loginLogs()->create([
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'login_at' => now(),
                ]);

                $this->dispatch('clientAuthenticated', clientId: $this->client->id);

                // GESTION DES DIFFÉRENTS STATUTS
                switch ($this->client->status) {
                    case 'approved':
                        return $this->redirect(route('products.index'), navigate: true);
                    case 'pending':
                        $this->step = 4; // Étape "En attente"
                        break;
                    case 'rejected':
                        $this->step = 5; // Étape "Rejeté"
                        break;
                }
            } else {
                $this->message = "Une erreur est survenue. Veuillez réessayer.";
            }
        } else {
            $this->message = "Le code de vérification est incorrect ou a expiré.";
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