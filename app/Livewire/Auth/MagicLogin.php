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

    // Persiste l'utilisateur trouvé entre les étapes pour ne pas avoir à le rechercher à nouveau
    public ?int $userIdToVerify = null; 

    // Gestion du temps pour le renvoi du code
    public int $cooldown = 0;
    protected $cooldownDuration = 60; // en secondes

    // Décrémente le cooldown chaque seconde
    public function tick(): void
    {
        if ($this->cooldown > 0) {
            $this->cooldown--;
        }
    }

    // Étape 1 : L'utilisateur entre son identifiant/email et demande un code
    public function sendCode(): void
    {
        $this->validate(['identifier' => 'required']);

        $user = Client::where('email', $this->identifier)
                      ->orWhere('identifiant_unique_somacif', $this->identifier)
                      ->first();
        
        if ($user) {
            $this->userType = 'client';
        } else {
            $user = Livreur::where('email', $this->identifier)->first();
            $this->userType = 'livreur';
        }

        if ($user) {
            $sentCode = $user->generateVerificationCode();
            $user->notify(new MagicLoginCodeNotification($sentCode));

            $this->userIdToVerify = $user->id; // On stocke l'ID de l'utilisateur
            $this->codeSent = true;
            $this->cooldown = $this->cooldownDuration; // On active le cooldown
            Notification::make()->title('Code envoyé !')->body('Un code de vérification a été envoyé. Veuillez vérifier vos e-mails.')->success()->send();
        } else {
            $this->addError('identifier', 'Aucun compte trouvé pour cet identifiant.');
        }
    }

    // Étape 2 : L'utilisateur entre le code reçu pour se connecter
    public function login(): void
    {
        $this->validate(['code' => 'required|numeric|digits:6']);

        $userModel = $this->userType === 'client' ? Client::class : Livreur::class;
        $user = $userModel::find($this->userIdToVerify);
        
        if ($user && Hash::check($this->code, $user->verification_code) && now()->lessThan($user->verification_code_expires_at)) {
            $guard = $this->userType;
            Auth::guard($guard)->login($user, true); // Le "true" active le "Se souvenir de moi"
            
            $user->forceFill([
                'verification_code' => null,
                'verification_code_expires_at' => null
            ])->save();
            
            $redirectRoute = ($guard === 'client') ? 'client.dashboard' : 'livreur.dashboard';
            $this->redirect(route($redirectRoute), navigate: true);
        } else {
            $this->addError('code', 'Code invalide ou expiré.');
        }
    }

    public function render()
    {
        // On indique à la vue d'utiliser le layout 'auth' qui contient les styles
        return view('livewire.auth.magic-login')
               ->layout('components.layouts.auth');
    }
}