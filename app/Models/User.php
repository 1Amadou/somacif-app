<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin', // Assurez-vous que ce champ existe dans votre migration
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean', // Cast en booléen pour une manipulation facile
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
     * Cette méthode n'est plus nécessaire à partir de Filament v3, 
     * mais nous la gardons par sécurité si une ancienne logique l'appelle.
     */
    public function canAccessFilament(): bool
    {
        return $this->is_admin;
    }

    /**
     * Indique si cet utilisateur peut accéder au panneau admin.
     * C'est la méthode principale utilisée par Filament v3.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // On autorise l'accès seulement si l'utilisateur est un admin
        // et que l'ID du panel est 'admin' (ce qui est le cas par défaut).
        return $panel->getId() === 'admin' && $this->is_admin;
    }
}