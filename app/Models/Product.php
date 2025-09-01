<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Product extends Model
{
    use HasFactory;

    // On garde tous vos champs personnalisés
    protected $fillable = [
        'category_id',
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
    ];

    protected $casts = [
        'images_galerie' => 'array',
        'is_visible' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function uniteDeVentes(): HasMany
    {
        return $this->hasMany(UniteDeVente::class);
    }

    /**
     * CORRECTION : Une relation "HasManyThrough".
     * Elle dit à Laravel : "Un Produit a plusieurs Inventaires À TRAVERS ses Unités de Vente".
     * C'est la relation correcte pour notre structure.
     */
    public function inventories(): HasManyThrough
    {
        return $this->hasManyThrough(Inventory::class, UniteDeVente::class);
    }
}