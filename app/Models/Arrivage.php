<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Arrivage extends Model
{
    use HasFactory;

    // On s'assure que 'details_produits' n'est PAS dans cette liste.
    protected $fillable = [
        'fournisseur_id',
        'numero_bon_livraison',
        'date_arrivage',
        'montant_total_arrivage',
        'total_quantite',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'date_arrivage' => 'datetime',
    ];

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ArrivageItem::class);
    }
}