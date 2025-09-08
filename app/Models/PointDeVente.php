<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne; // Ajout pour la nouvelle relation

class PointDeVente extends Model
{
    use HasFactory;
    
    protected $table = 'point_de_ventes';
    
    protected $fillable = [
        'nom', 
        'type', 
        'adresse', 
        'telephone', 
        'horaires', 
        'Maps_link',
        'responsable_id',
    ];

    /**
     * *** MODIFICATION 1: Automatisation de la gestion du lieu de stockage lié ***
     * Cette méthode spéciale s'assure que pour chaque PointDeVente, il existe
     * un LieuDeStockage correspondant, et que leurs informations restent synchronisées.
     */
    protected static function booted(): void
    {
        // Quand un nouveau Point de Vente est créé...
        static::created(function (PointDeVente $pointDeVente) {
            // ...on crée automatiquement son jumeau dans la table des lieux de stockage.
            $pointDeVente->lieuDeStockage()->create([
                'nom' => $pointDeVente->nom,
                'type' => 'point_de_vente',
            ]);
        });

        // Quand un Point de Vente est mis à jour...
        static::updated(function (PointDeVente $pointDeVente) {
            // ...on met aussi à jour le nom du lieu de stockage associé.
            $pointDeVente->lieuDeStockage()->update([
                'nom' => $pointDeVente->nom,
            ]);
        });

        // Quand un Point de Vente est sur le point d'être supprimé...
        static::deleting(function (PointDeVente $pointDeVente) {
            // ...on supprime aussi le lieu de stockage associé pour ne pas laisser de données orphelines.
            $pointDeVente->lieuDeStockage()->delete();
        });
    }

    /**
     * Définit la relation avec le client qui est responsable du point de vente.
     * Logique conservée.
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'responsable_id');
    }

    /**
     * Un point de vente possède plusieurs lignes d'inventaire.
     * Logique conservée, mais elle sera maintenant utilisée via le lieu de stockage.
     */
    public function inventories(): HasMany
    {
        // Cette relation directe est moins utile maintenant, mais on peut la garder.
        // La vraie relation passera par LieuDeStockage.
        return $this->hasMany(Inventory::class);
    }

    /**
     * *** MODIFICATION 2: Ajout de la relation clé vers le LieuDeStockage ***
     * Un Point de Vente A UN ET UN SEUL lieu de stockage.
     */
    public function lieuDeStockage(): HasOne
    {
        return $this->hasOne(LieuDeStockage::class);
    }
}