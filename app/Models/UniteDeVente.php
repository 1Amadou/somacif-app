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

    // Je vous recommande de ne pas utiliser la propriété $with,
    // car elle peut impacter les performances de manière imprévue.
    // Il est préférable de charger les relations manuellement avec `with()` ou `load()`.
    // protected $with = ['product']; 

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
    
    // Ajout des relations manquantes pour le suivi
    // On suppose que les stocks clients sont dans une table `inventories`
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'unite_de_vente_id');
    }

    // On suppose que les détails des ventes sont dans une table `reglement_items`
    public function reglementItems(): HasMany
    {
        return $this->hasMany(ReglementItem::class, 'unite_de_vente_id');
    }
}