<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VenteDirecte extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_facture',
        'client_id',
        'date_vente',
        'montant_total',
        'methode_paiement',
        'notes',
        'user_id',
    ];

    /**
     * S'exécute automatiquement lors de la création d'une Vente Directe.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($venteDirecte) {
            // Si le numéro de facture n'a pas été défini par le formulaire, on en crée un.
            if (empty($venteDirecte->numero_facture)) {
                $venteDirecte->numero_facture = 'FD-' . strtoupper(uniqid());
            }
        });
    }

    // --- RELATIONS ---
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VenteDirecteItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}