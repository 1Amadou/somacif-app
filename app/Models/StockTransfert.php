<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfert extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_type',
        'source_id',
        'destination_type',
        'destination_id',
        'notes',
        'user_id',
    ];

    /**
     * *** CORRECTION : Ajout de la relation 'details' ***
     * Un transfert de stock est composé de plusieurs lignes de produits (détails).
     * C'est cette relation qui manquait.
     */
    public function details(): HasMany
    {
        return $this->hasMany(StockTransfertDetail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accesseurs pour obtenir les noms de la source et de la destination dynamiquement
    public function getSourceAttribute(): Model|null
    {
        if ($this->source_type === 'entrepot') {
            return LieuDeStockage::find(cache()->get('entrepot_principal_id'));
        }
        return PointDeVente::find($this->source_id);
    }

    public function getDestinationAttribute(): Model|null
    {
        if ($this->destination_type === 'entrepot') {
            return LieuDeStockage::find(cache()->get('entrepot_principal_id'));
        }
        return PointDeVente::find($this->destination_id);
    }
}