<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reglement extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'montant_verse',
        'methode_paiement',
        'notes',
        'date_reglement',
        'point_de_vente_id', // Nécessaire pour l'observer
        'order_id',          // NOUVEAU : La commande principale du règlement
        'montant_calcule',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    // Un règlement est lié à UNE commande principale (pour le déstockage)
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Un règlement peut être imputé sur plusieurs commandes (partie purement comptable)
    public function imputedOrders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_reglement');
    }
    
    public function details(): HasMany
    {
        return $this->hasMany(DetailReglement::class);
    }

    public function pointDeVente(): BelongsTo
    {
        return $this->belongsTo(PointDeVente::class);
    }
}