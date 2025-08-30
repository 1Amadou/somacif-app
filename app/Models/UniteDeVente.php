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

    /**
     * CORRECTION DÉFINITIVE DE ROBUSTESSE :
     * Construit un nom complet et lisible qui ne plantera JAMAIS,
     * même si le produit parent n'existe plus dans la base de données.
     */
    public function getNomCompletAttribute(): string
    {
        // Utilise l'opérateur "null coalescing" pour fournir une valeur par défaut
        // si la relation 'product' ou son nom est null.
        $productName = $this->product->nom ?? '[PRODUIT MANQUANT]';

        return "{$productName} - {$this->calibre} ({$this->nom_unite})";
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'unite_de_vente_id');
    }

    /**
     * Correction de la relation pour pointer vers le bon modèle DetailReglement.
     */
    public function detailReglements(): HasMany
    {
        return $this->hasMany(DetailReglement::class, 'unite_de_vente_id');
    }
}