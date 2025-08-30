<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Livreur extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom', 'prenom', 'telephone', 'email', 'password',
        // Champs pour l'authentification sans mot de passe
        'verification_code', 'verification_code_expires_at',
    ];

    protected $hidden = ['password', 'remember_token', 'verification_code'];
    
    protected $casts = [
        'password' => 'hashed',
        'verification_code_expires_at' => 'datetime',
    ];
    
    public function getFullNameAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    /**
     * LOGIQUE : Génère un code de vérification à 6 chiffres.
     */
    public function generateVerificationCode(): void
    {
        $this->verification_code = random_int(100000, 999999);
        $this->verification_code_expires_at = now()->addMinutes(10);
        $this->save();
    }
}