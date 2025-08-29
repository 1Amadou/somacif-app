<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailReglement extends Model
{
    use HasFactory;

    // Le nom de la table est 'details_reglement' (avec un s)
    protected $table = 'details_reglement';

    protected $fillable = [
        'reglement_id',
        'unite_de_vente_id',
        'quantite_vendue',
        'prix_de_vente_unitaire',
    ];

    public function reglement(): BelongsTo
    {
        return $this->belongsTo(Reglement::class);
    }

    public function uniteDeVente(): BelongsTo
    {
        return $this->belongsTo(UniteDeVente::class);
    }
}