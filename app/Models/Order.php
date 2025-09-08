<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;


class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'point_de_vente_id', 'livreur_id', 'numero_commande',
        'statut', 'montant_total', 'notes', 'statut_paiement', 'montant_paye',
        'is_vente_directe', 'client_confirmed_at', 'livreur_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'statut' => OrderStatusEnum::class,
            'statut_paiement' => PaymentStatusEnum::class, 
            'is_vente_directe' => 'boolean',
            'client_confirmed_at' => 'datetime',
            'livreur_confirmed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function pointDeVente(): BelongsTo { return $this->belongsTo(PointDeVente::class); }
    public function livreur(): BelongsTo { return $this->belongsTo(Livreur::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function reglements(): BelongsToMany { return $this->belongsToMany(Reglement::class, 'order_reglement'); }

    public function updatePaymentStatus(): void
{
    
    $this->load('reglements');
    
    $montantTotal = $this->montant_total;
    $totalVerse = $this->reglements->sum('montant_verse');

    if ($totalVerse >= $montantTotal) {
        $this->statut_paiement = 'Complètement réglé';
    } elseif ($totalVerse > 0 && $totalVerse < $montantTotal) {
        $this->statut_paiement = 'Partiellement réglé';
    } else {
        $this->statut_paiement = 'non_payee';
    }

    // On sauvegarde sans redéclencher d'observers pour éviter les boucles infinies.
    $this->saveQuietly();
}
    
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            if (empty($order->numero_commande)) {
                $order->numero_commande = 'CMD-' . date('Ymd') . '-' . strtoupper(uniqid());
            }
            if (empty($order->statut_paiement)) {
                $order->statut_paiement = PaymentStatusEnum::NON_PAYEE;
            }
        });
    }

    public function getTotalVerseAttribute(): float
{
    // Calcule la somme de tous les montants versés pour cette commande.
    return $this->reglements()->sum('montant_verse');
}

public function getSoldeRestantAttribute(): float
{
    // Calcule la différence entre le total de la commande et ce qui a été versé.
    return $this->montant_total - $this->total_verse;
}
}