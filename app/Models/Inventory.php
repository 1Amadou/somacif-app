<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    // On précise le nom de la table, car Laravel chercherait "inventories" par défaut
    protected $table = 'inventory';

    protected $fillable = [
        'point_de_vente_id',
        'unite_de_vente_id',
        'quantite_stock',
    ];

    public function pointDeVente(): BelongsTo
    {
        return $this->belongsTo(PointDeVente::class, 'point_de_vente_id');
    }

    public function uniteDeVente(): BelongsTo
    {
        return $this->belongsTo(UniteDeVente::class);
    }
}