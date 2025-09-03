<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenteDirecteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'vente_directe_id',
        'unite_de_vente_id',
        'quantite',
        'prix_unitaire',
    ];

    public function venteDirecte(): BelongsTo
    {
        return $this->belongsTo(VenteDirecte::class);
    }

    public function uniteDeVente(): BelongsTo
    {
        return $this->belongsTo(UniteDeVente::class);
    }
}