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
        // 'stock', // On enlève cette ligne
        'calibre',
        'prix_grossiste',
        'prix_hotel_restaurant',
        'prix_particulier',
    ];

    /**
     * Accesseur pour calculer le stock total à partir des inventaires.
     * @return int|float
     */
    public function getStockAttribute()
    {
        return $this->inventories()->sum('quantite');
    }

    public function getNomCompletAttribute(): string
    {
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
    
    public function detailReglements(): HasMany
    {
        return $this->hasMany(DetailReglement::class, 'unite_de_vente_id');
    }
}