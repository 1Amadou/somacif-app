<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'slug',
        'description_courte',
        'description_longue',
        'calibres',
        'origine',
        'poids_moyen',
        'conservation',
        'infos_nutritionnelles',
        'idee_recette',
        'image_principale',
        'images_galerie',
        'is_visible',
        'meta_titre',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'images_galerie' => 'array',
            'is_visible' => 'boolean',
            'calibres' => 'array',
        ];
    }

    public function uniteDeVentes(): HasMany
    {
        return $this->hasMany(UniteDeVente::class);
    }

    public function pointsDeVenteStock(): BelongsToMany
    {
        return $this->belongsToMany(PointDeVente::class, 'inventory')
                    ->withPivot('quantite_stock')
                    ->withTimestamps();
    }
}