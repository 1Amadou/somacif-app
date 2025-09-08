<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute; // Ajout pour les accesseurs modernes
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UniteDeVente extends Model
{
    use HasFactory;

    // *** MODIFICATION 1: Ajout de 'nom_complet' aux champs remplissables ***
    protected $fillable = [
        'product_id',
        'nom_unite',
        'calibre',
        'nom_complet', // Ajouté pour permettre la sauvegarde depuis le formulaire Filament
        'prix_particulier',
        'prix_grossiste',
        'prix_hotel_restaurant',
    ];

    /**
     * *** MODIFICATION 2: Nouvel accesseur pour le stock de l'entrepôt principal ***
     * Remplace l'ancienne méthode 'getStockPrincipalAttribute'.
     * Cette nouvelle version est plus claire, plus performante et utilise notre nouvelle structure.
     */
    protected function stockEntrepôtPrincipal(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                // On cherche l'ID de l'entrepôt principal (on le met en cache pour la performance)
                $entrepotId = cache()->rememberForever('entrepot_principal_id', function () {
                    return LieuDeStockage::where('type', 'entrepot')->value('id');
                });

                // Si l'entrepôt n'existe pas, le stock est de 0
                if (!$entrepotId) {
                    return 0;
                }

                // On cherche la ligne d'inventaire pour cette U.V. DANS l'entrepôt principal
                $inventory = $this->inventories()->where('lieu_de_stockage_id', $entrepotId)->first();

                // On retourne la quantité, ou 0 si aucune ligne n'existe.
                return $inventory ? $inventory->quantite_stock : 0;
            }
        );
    }
    
    /**
     * Accesseur pour le nom complet, robuste et clair.
     * Format : "NomProduit (NomUnité, Calibre)"
     * Conservé tel quel, car la logique est bonne.
     */
    public function getNomCompletAttribute(): string
    {
        // La génération du nom complet est maintenant principalement gérée dans Filament,
        // mais cet accesseur reste utile pour l'affichage.
        return $this->attributes['nom_complet'] ?? '';
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

    // *** MODIFICATION 3: Ajout des relations manquantes pour les Observers ***
    public function arrivageItems(): HasMany
    {
        // La table pivot s'appelle 'arrivage_unite_de_vente'
        return $this->hasMany(ArrivageItem::class, 'unite_de_vente_id');
    }

    public function venteDirecteItems(): HasMany
    {
        return $this->hasMany(VenteDirecteItem::class, 'unite_de_vente_id');
    }
    public function getNomCompletWithStockAttribute(): string
{
    return $this->nom_complet . ' (Stock: ' . $this->stock_entrepôt_principal . ')';
}
}