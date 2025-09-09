<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom', 'email', 'password', 'telephone', 'type', 
        'identifiant_unique_somacif', 'status',
        'verification_code', 'verification_code_expires_at',
    ];

    protected $hidden = ['password', 'remember_token', 'verification_code'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'verification_code' => 'hashed', // Hachage du code de vérification
        'verification_code_expires_at' => 'datetime',
    ];

    public function pointsDeVente(): HasMany
    {
        return $this->hasMany(PointDeVente::class, 'responsable_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reglements(): HasMany
    {
        return $this->hasMany(Reglement::class);
    }

    public function generateVerificationCode(): int
    {
        $code = random_int(100000, 999999);
        $this->verification_code = $code; // Laravel va le hacher grâce au cast
        $this->verification_code_expires_at = now()->addMinutes(10);
        $this->save();
        return $code; // Retourne le code en clair pour l'envoi
    }
    public function loginLogs(): HasMany
{
    return $this->hasMany(LoginLog::class)->latest('login_at');
}
}