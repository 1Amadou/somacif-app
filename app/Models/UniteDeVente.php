<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class UniteDeVente extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'nom_unite',
        'prix_unitaire',
        'stock',
        'calibre',
        'prix_grossiste',
        'prix_hotel_restaurant',
        'prix_particulier',
    ];

    // On ajoute 'product' pour qu'il soit toujours chargé avec l'unité de vente
    protected $with = ['product'];

    /**
     * Crée un nom complet et descriptif pour l'unité de vente.
     * Exemple: "Tilapia (Carpe) - 200-300g (10kg)"
     */
    public function getNomCompletAttribute(): string
    {
        return "{$this->product->nom} - {$this->calibre} ({$this->nom_unite})";
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    
}