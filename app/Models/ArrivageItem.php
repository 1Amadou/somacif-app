<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArrivageItem extends Model
{
    use HasFactory;

    // On spécifie la table car le nom est un peu particulier
    protected $table = 'arrivage_items';

    public $timestamps = false; // La table pivot n'a pas besoin de timestamps

    protected $fillable = [
        'arrivage_id',
        'unite_de_vente_id',
        'quantite',
        'prix_achat_unitaire',
    ];

    public function arrivage(): BelongsTo
    {
        return $this->belongsTo(Arrivage::class);
    }

    public function uniteDeVente(): BelongsTo
    {
        return $this->belongsTo(UniteDeVente::class);
    }
}