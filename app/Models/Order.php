<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * CORRECTION CRUCIALE : Ajout de 'montant_paye' au tableau $fillable.
     * Sans cela, Laravel empêche par sécurité la mise à jour automatique de ce champ.
     * C'était la cause principale du décalage de la logique.
     */
    protected $fillable = [
        'client_id',
        'point_de_vente_id',
        'livreur_id',
        'numero_commande',
        'statut',
        'montant_total',
        'notes',
        'statut_paiement',
        'montant_paye', 
        'is_vente_directe',
        'client_confirmed_at',
        'livreur_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'client_confirmed_at' => 'datetime',
            'livreur_confirmed_at' => 'datetime',
            'is_vente_directe' => 'boolean',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function pointDeVente(): BelongsTo { return $this->belongsTo(PointDeVente::class); }
    public function livreur(): BelongsTo { return $this->belongsTo(Livreur::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }

    /**
     * AMÉLIORATION : Relation BelongsToMany explicite pour une robustesse maximale.
     * C'est le lien utilisé par la page de la commande pour afficher les paiements.
     */
    public function reglements(): BelongsToMany
    {
        return $this->belongsToMany(Reglement::class, 'order_reglement', 'order_id', 'reglement_id');
    }
}