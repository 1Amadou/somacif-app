<?php

namespace App\Livewire\Auth;

use App\Models\Client;
use App\Models\Livreur;
use App\Notifications\MagicLoginCodeNotification;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MagicLogin extends Component
{
    public ?string $identifier = '';
    public ?string $code = '';
    public bool $codeSent = false;
    public ?string $userType = null;
    public $userToVerify = null;

    // Étape 1 : L'utilisateur entre son identifiant/email
    public function sendCode()
    {
        $this->validate(['identifier' => 'required']);

        $user = Client::where('email', $this->identifier)
                      ->orWhere('identifiant_unique_somacif', $this->identifier)
                      ->first();
        $this->userType = 'client';
        
        if (!$user) {
            $user = Livreur::where('email', $this->identifier)->first();
            $this->userType = 'livreur';
        }

        if ($user) {
            $user->generateVerificationCode();
            $user->notify(new MagicLoginCodeNotification($user->verification_code));

            $this->userToVerify = $user;
            $this->codeSent = true;
            Notification::make()->title('Code envoyé !')->body('Un code de vérification a été envoyé à votre adresse email.')->success()->send();
        } else {
            $this->addError('identifier', 'Aucun compte trouvé pour cet identifiant.');
        }
    }

    // Étape 2 : L'utilisateur entre le code reçu
    public function verifyCode()
    {
        $this->validate(['code' => 'required|numeric']);

        $user = $this->userToVerify;

        if ($user && $user->verification_code == $this->code && now()->lessThan($user->verification_code_expires_at)) {
            $guard = $this->userType; // 'client' ou 'livreur'
            Auth::guard($guard)->login($user, true);
            $user->update(['verification_code' => null, 'verification_code_expires_at' => null]);
            
            $redirectRoute = $guard === 'client' ? 'client.dashboard' : 'livreur.dashboard';
            return $this->redirect(route($redirectRoute), navigate: true);
        } else {
            $this->addError('code', 'Code invalide ou expiré.');
        }
    }

    public function render()
    {
        return view('livewire.auth.magic-login')->layout('components.layouts.auth');
    }
}