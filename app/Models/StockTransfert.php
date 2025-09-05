<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransfert extends Model
{
    use HasFactory;

    protected $table = 'stock_transferts';

    /**
     * CORRECTION : La liste des champs correspond maintenant à la migration.
     */
    protected $fillable = [
        'source_point_de_vente_id',
        'destination_point_de_vente_id',
        'user_id',
        'notes',
        'details',
    ];

    /**
     * Laravel va automatiquement convertir la colonne JSON 'details' en tableau PHP.
     */
    protected $casts = [
        'details' => 'array',
    ];

    /**
     * CORRECTION : La relation 'details' qui causait l'erreur a été supprimée.
     * Les détails sont maintenant directement dans la colonne 'details'.
     */

    public function sourcePointDeVente(): BelongsTo
    {
        return $this->belongsTo(PointDeVente::class, 'source_point_de_vente_id');
    }

    public function destinationPointDeVente(): BelongsTo
    {
        return $this->belongsTo(PointDeVente::class, 'destination_point_de_vente_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}