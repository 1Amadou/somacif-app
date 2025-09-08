<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LieuDeStockage extends Model
{
    use HasFactory;

    // On définit la table car le nom est un peu particulier
    protected $table = 'lieux_de_stockage';

    // On autorise le remplissage de ces champs
    protected $fillable = [
        'nom',
        'type',
        'is_active',
        'point_de_vente_id',
    ];

    /**
     * Un lieu de stockage peut avoir plusieurs lignes d'inventaire.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Un lieu de stockage de type 'point_de_vente' est lié à un PointDeVente.
     */
    public function pointDeVente(): BelongsTo
    {
        return $this->belongsTo(PointDeVente::class);
    }
}