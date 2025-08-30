<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom', 'email', 'password', 'telephone', 'type', 
        'identifiant_unique_somacif', 'statut',
        // Champs pour l'authentification sans mot de passe
        'verification_code', 'verification_code_expires_at',
    ];

    protected $hidden = ['password', 'remember_token', 'verification_code'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'verification_code_expires_at' => 'datetime',
    ];

    /**
     * Un client peut avoir plusieurs points de vente.
     * C'est la relation la plus importante pour la distribution.
     */
    public function pointsDeVente(): HasMany
    {
        return $this->hasMany(PointDeVente::class, 'responsable_id');
    }

    /**
     * Un client peut passer plusieurs commandes.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Un client peut effectuer plusieurs règlements.
     */
    public function reglements(): HasMany
    {
        return $this->hasMany(Reglement::class);
    }
    /**
     * 
     */
    public function generateVerificationCode(): void
    {
        $this->verification_code = random_int(100000, 999999);
        $this->verification_code_expires_at = now()->addMinutes(10);
        $this->save();
    }
}