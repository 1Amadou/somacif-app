<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\StockManager; // Ajout pour utiliser le service de stock

class UniteDeVente extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'nom_unite',
        'calibre',
        'prix_particulier',
        'prix_grossiste',
        'prix_hotel_restaurant',
    ];

    /**
     * Accesseur pour obtenir le stock de l'entrepôt principal.
     * C'est le stock de référence pour les nouvelles commandes.
     */
    public function getStockPrincipalAttribute(): float
    {
        $stockManager = app(StockManager::class);
        return $stockManager->getInventoryStock($this, null); // null représente l'entrepôt principal
    }

    /**
     * CORRIGÉ : Accesseur pour le nom complet, robuste et clair.
     * Format : "NomProduit (NomUnité, Calibre)"
     */
    public function getNomCompletAttribute(): string
    {
        $productName = $this->product->nom ?? 'Produit Inconnu';
        $uniteName = $this->nom_unite ?? 'N/A';
        $calibre = $this->calibre ?? 'N/A';

        return "{$productName} ({$uniteName}, {$calibre})";
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