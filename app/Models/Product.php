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
        'origine',
        'conservation',
        'infos_nutritionnelles',
        'idee_recette',
        'image_principale',
        'images_galerie',
        'is_visible',
        'meta_titre',
        'meta_description',
        // Le champ 'calibres' a été déplacé vers UniteDeVente pour plus de flexibilité.
    ];

    protected $casts = [
        'images_galerie' => 'array',
        'is_visible' => 'boolean',
    ];

    /**
     * Un Produit (ex: Tilapia) peut avoir plusieurs déclinaisons de vente.
     */
    public function uniteDeVentes(): HasMany
    {
        return $this->hasMany(UniteDeVente::class);
    }

    /**
     * Relation pour voir le stock d'un produit à travers tous les points de vente.
     * C'est une relation indirecte pour les rapports.
     */
    public function pointsDeVenteStock(): BelongsToMany
    {
        return $this->belongsToMany(PointDeVente::class, 'inventory')
                    ->withPivot('quantite_stock')
                    ->withTimestamps();
    }
}