<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // N'oublie pas d'importer BelongsToMany

class Reglement extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'date_reglement',
        'montant_verse',
        'montant_calcule',
        'methode_paiement',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'date_reglement' => 'date',
    ];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function details(): HasMany { return $this->hasMany(DetailReglement::class); }

    /**
     * AMÉLIORATION : Relation BelongsToMany explicite pour une robustesse maximale.
     * C'est le lien utilisé par le formulaire de règlement pour enregistrer les commandes.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_reglement', 'reglement_id', 'order_id');
    }
}