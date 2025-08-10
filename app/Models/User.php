<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel; // bien importer la classe Panel ici
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Indique si cet utilisateur peut accéder à Filament.
     */
    public function canAccessFilament(): bool
    {
        // Par exemple, autoriser toujours l'accès
        return true;
    }

    /**
     * Indique si cet utilisateur peut accéder au panneau admin.
     * Le panel est passé en paramètre pour permettre une vérification spécifique si besoin.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Exemple : toujours autoriser
        return true;

        // Ou logiques personnalisées, ex. selon $panel->id
        // return $panel->id === 'admin' && $this->hasVerifiedEmail();
    }
}
