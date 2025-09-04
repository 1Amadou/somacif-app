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
        'verification_code', 'verification_code_expires_at',
    ];

    protected $hidden = ['password', 'remember_token', 'verification_code'];
    
    protected $casts = [
        'password' => 'hashed',
        'verification_code' => 'hashed', // Hachage du code de vérification
        'verification_code_expires_at' => 'datetime',
    ];
    
    public function getFullNameAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function generateVerificationCode(): int
    {
        $code = random_int(100000, 999999);
        $this->verification_code = $code; // Laravel va le hacher grâce au cast
        $this->verification_code_expires_at = now()->addMinutes(10);
        $this->save();
        return $code; // Retourne le code en clair pour l'envoi
    }
}