<?php

namespace App\Livewire\Auth;

use App\Models\Client;
use App\Models\Livreur;
use App\Notifications\MagicLoginCodeNotification;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class MagicLogin extends Component
{
    public ?string $identifier = '';
    public ?string $code = '';
    public bool $codeSent = false;
    public ?string $userType = null;
    public $userToVerify = null;

    // --- NOUVEAUX ATTRIBUTS POUR LA GESTION DU TEMPS ---
    public int $cooldown = 0;
    protected $cooldownDuration = 60; // Durée du cooldown en secondes

    // Méthode pour incrémenter le temps et réactiver le bouton
    public function tick(): void
    {
        if ($this->cooldown > 0) {
            $this->cooldown--;
        }
    }

    // Étape 1 : L'utilisateur entre son identifiant/email
    public function sendCode(): void
    {
        // Réinitialiser le cooldown si on renvoie le code
        $this->cooldown = $this->cooldownDuration;
        
        $this->validate(['identifier' => 'required']);

        $user = Client::where('email', $this->identifier)
                      ->orWhere('identifiant_unique_somacif', $this->identifier)
                      ->first();
        
        if (!$user) {
            $user = Livreur::where('email', $this->identifier)->first();
            $this->userType = 'livreur';
        } else {
            $this->userType = 'client';
        }

        if ($user) {
            $sentCode = $user->generateVerificationCode();
            $user->notify(new MagicLoginCodeNotification($sentCode));

            $this->userToVerify = $user;
            $this->codeSent = true;
            Notification::make()->title('Code envoyé !')->body('Un code de vérification a été envoyé à votre adresse email.')->success()->send();
        } else {
            $this->addError('identifier', 'Aucun compte trouvé pour cet identifiant.');
        }
    }

    // Étape 2 : L'utilisateur entre le code reçu
    public function verifyCode(): void
    {
        $this->validate(['code' => 'required|numeric']);

        $user = $this->userToVerify;
        
        if ($user && Hash::check($this->code, $user->verification_code) && now()->lessThan($user->verification_code_expires_at)) {
            $guard = $this->userType;
            Auth::guard($guard)->login($user, true);
            $user->update(['verification_code' => null, 'verification_code_expires_at' => null]);
            
            $redirectRoute = ($guard === 'client') ? 'client.dashboard' : 'livreur.dashboard';
            $this->redirect(route($redirectRoute), navigate: true);
        } else {
            $this->addError('code', 'Code invalide ou expiré.');
        }
    }

    public function render()
    {
        return view('livewire.auth.magic-login')->layout('components.layouts.auth');
    }
}