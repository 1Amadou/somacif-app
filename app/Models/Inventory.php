<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    /**
     * Lie définitivement ce modèle à la table 'inventories'.
     */
    protected $table = 'inventories';

    /**
     * *** MODIFICATION 1: Mise à jour des champs remplissables ***
     * On remplace 'point_de_vente_id' par 'lieu_de_stockage_id'
     * pour correspondre à notre nouvelle structure de base de données.
     */
    protected $fillable = [
        'lieu_de_stockage_id', // Modifié
        'unite_de_vente_id',
        'quantite_stock',
    ];

    /**
     * *** MODIFICATION 2: Remplacement de la relation ***
     * Une ligne d'inventaire n'appartient plus à un PointDeVente,
     * mais à un LieuDeStockage (qui peut être un entrepôt OU un point de vente).
     */
    public function lieuDeStockage(): BelongsTo
    {
        return $this->belongsTo(LieuDeStockage::class);
    }

    /**
     * La relation vers l'UniteDeVente reste inchangée et correcte.
     */
    public function uniteDeVente(): BelongsTo
    {
        return $this->belongsTo(UniteDeVente::class);
    }
}