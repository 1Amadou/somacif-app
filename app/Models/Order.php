<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 
        'livreur_id', 
        'numero_commande', 
        'statut', 
        'delivery_address',
        'montant_total', 
        'amount_paid', 
        'due_date', 
        'notes', 
        'client_confirmed_at', 
        'livreur_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'client_confirmed_at' => 'datetime',
            'livreur_confirmed_at' => 'datetime',
            'due_date' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getRemainingBalanceAttribute(): float
    {
        return $this->montant_total - $this->amount_paid;
    }
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    /**
     * Récupère tous les règlements du client associé à cette commande.
     */
    // public function reglements(): HasManyThrough
    // {
    //     // On dit à Laravel : "Trouve les règlements (Reglement) EN PASSANT PAR le client (Client)"
    //     return $this->hasManyThrough(Reglement::class, Client::class, 'id', 'client_id', 'client_id', 'id');
    // }
    public function reglements()
{
    return $this->belongsToMany(Reglement::class);
}
    
}
