<?php

namespace App\Livewire\Auth;

use App\Models\Client;
use App\Notifications\SmsNotification;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginPage extends Component
{
    public ?string $identifiant = '';
    public ?string $password = '';
    public ?string $code = '';

    public bool $codeSent = false;
    public ?Client $clientToVerify = null;

    // Étape 1 : Le client entre son identifiant et mot de passe
    public function attemptLogin()
    {
        $this->validate([
            'identifiant' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'identifiant_unique_somacif' => $this->identifiant,
            'password' => $this->password,
        ];

        if (Auth::guard('client')->attempt($credentials)) {
            $client = Client::find(Auth::guard('client')->id());
            
            // On envoie le code de vérification
            $client->generateVerificationCode();
            $client->notify(new SmsNotification('client.verify.sms', ['code' => $client->verification_code]));

            $this->clientToVerify = $client;
            $this->codeSent = true;
            $this->password = ''; // On efface le mot de passe
            
            Notification::make()->title('Code envoyé !')->body('Un code de vérification a été envoyé à votre téléphone.')->success()->send();
        } else {
            $this->addError('identifiant', 'Identifiant ou mot de passe incorrect.');
        }
    }

    // Étape 2 : Le client entre le code reçu
    public function verifyCode()
    {
        $this->validate(['code' => 'required|numeric']);

        if ($this->clientToVerify) {
            if ($this->clientToVerify->verification_code == $this->code && now()->lessThan($this->clientToVerify->verification_code_expires_at)) {
                
                // Le code est bon, on connecte l'utilisateur
                Auth::guard('client')->login($this->clientToVerify, true);
                
                // On efface le code pour la sécurité
                $this->clientToVerify->update(['verification_code' => null, 'verification_code_expires_at' => null]);

                return $this->redirect(route('client.dashboard'), navigate: true);
            } else {
                $this->addError('code', 'Code invalide ou expiré.');
            }
        }
    }

    public function render()
    {
        return view('livewire.auth.login-page')
            ->layout('components.layouts.auth');
    }
}