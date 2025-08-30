<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'point_de_vente_id',
        'unite_de_vente_id',
        'quantite_stock',
    ];

    protected $casts = [
        'quantite_stock' => 'integer',
    ];

    public function pointDeVente(): BelongsTo
    {
        return $this->belongsTo(PointDeVente::class, 'point_de_vente_id');
    }

    public function uniteDeVente(): BelongsTo
    {
        return $this->belongsTo(UniteDeVente::class);
    }

    public function scopeOfPointDeVente($query, $pointDeVenteId)
    {
        return $query->where('point_de_vente_id', $pointDeVenteId);
    }
}
