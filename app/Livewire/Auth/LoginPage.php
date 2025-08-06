<?php

namespace App\Livewire\Auth;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginPage extends Component
{
    public string $tab = 'partenaire';

    // Propriétés pour la connexion Partenaire
    public string $identifiant_unique = '';
    public bool $codeSent = false;
    public string $verification_code = '';

    // Propriétés pour la connexion Livreur
    public string $phone = '';
    public string $password = '';

    public function sendVerificationCode()
    {
        $this->validate(['identifiant_unique' => 'required|string']);
        $client = Client::where('identifiant_unique_somacif', $this->identifiant_unique)->first();

        if ($client) {
            $code = random_int(100000, 999999);
            session(['verification_code' => $code, 'client_id_to_verify' => $client->id]);
            session()->flash('test_code', $code);
            // Ici, on enverrait le vrai SMS avec le système de notification
            $this->codeSent = true;
        } else {
            $this->addError('identifiant_unique', 'Cet identifiant est inconnu.');
        }
    }

    public function verifyClientCode()
    {
        $this->validate(['verification_code' => 'required|numeric|digits:6']);
        if ($this->verification_code == session('verification_code')) {
            $clientId = session('client_id_to_verify');
            $client = Client::find($clientId);
            session(['authenticated_client_id' => $clientId]);
            session()->forget(['verification_code', 'client_id_to_verify']);
            
            $client->loginLogs()->create([
                'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(), 'login_at' => now(),
            ]);

            return redirect()->intended(route('client.dashboard'));
        }
        $this->addError('verification_code', 'Le code est incorrect.');
    }

    public function loginLivreur()
    {
        $credentials = $this->validate(['phone' => 'required', 'password' => 'required']);
        if (Auth::guard('livreur')->attempt($credentials)) {
            request()->session()->regenerate();
            return redirect()->intended(route('livreur.dashboard'));
        }
        $this->addError('phone', 'Les informations d\'identification ne correspondent pas.');
    }

    public function render()
    {
        return view('livewire.auth.login-page')
            ->layout('components.layouts.app', ['metaTitle' => 'Connexion - SOMACIF']);
    }
}