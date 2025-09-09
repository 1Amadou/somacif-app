<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'point_de_vente_id', 'livreur_id', 'numero_commande',
        'statut', 'montant_total', 'notes', 'is_vente_directe',
        'statut_paiement', 'montant_paye', 'facture_proforma_print_count', 'bon_livraison_print_count'
    ];

    protected function casts(): array
    {
        return [
            'statut' => OrderStatusEnum::class,
            'statut_paiement' => PaymentStatusEnum::class,
            'is_vente_directe' => 'boolean',
        ];
    }
    
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            if (empty($order->statut_paiement)) {
                $order->statut_paiement = PaymentStatusEnum::NON_PAYEE;
            }
            if (is_null($order->montant_total)) {
                $order->montant_total = 0;
            }
        });
    }

    // --- RELATIONS ---
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function pointDeVente(): BelongsTo { return $this->belongsTo(PointDeVente::class); }
    public function livreur(): BelongsTo { return $this->belongsTo(Livreur::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function reglements(): HasMany { return $this->hasMany(Reglement::class); }
    public function transfertsOrigine(): HasMany { return $this->hasMany(StockTransfert::class, 'order_id'); }

    // --- LOGIQUE MÉTIER ---
    public function updatePaymentStatus(): void
    {
        $quantiteCommandee = $this->quantite_actuelle;
        $quantiteReglee = $this->quantite_reglee;
        $totalVerse = $this->reglements()->sum('montant_verse');
        
        $this->montant_paye = $totalVerse;

        if ($quantiteCommandee > 0 && $quantiteReglee >= $quantiteCommandee) {
            $this->statut_paiement = PaymentStatusEnum::COMPLETEMENT_REGLE;
        } elseif ($quantiteReglee > 0) {
            $this->statut_paiement = PaymentStatusEnum::PARTIELLEMENT_REGLE;
        } else {
            $this->statut_paiement = PaymentStatusEnum::NON_PAYEE;
        }
        $this->saveQuietly();
    }
    
    public function recalculateTotal(): void
    {
        $this->loadMissing('items');
        $total = $this->items->sum(function ($item) {
            return $item->quantite * $item->prix_unitaire;
        });
        $this->update(['montant_total' => $total]);
    }

    // --- ACCESSEURS (GETTERS) POUR LA VUE ---
    public function getQuantiteInitialeAttribute(): int
    {
        return $this->items()->sum('quantite');
    }

    public function getQuantiteRegleeAttribute(): int
    {
        return $this->reglements()->with('details')->get()->sum(fn ($reglement) => $reglement->details->sum('quantite_vendue'));
    }
    
    /**
     * CORRECTION DE L'ERREUR :
     * On ne traite plus 'details' comme une relation, mais comme une propriété (tableau).
     */
    public function getQuantiteTransfereeAttribute(): int
    {
        $this->loadMissing('transfertsOrigine');
        
        return $this->transfertsOrigine->sum(function ($transfert) {
            // On somme directement la clé 'quantite' dans le tableau 'details'
            return collect($transfert->details)->sum('quantite');
        });
    }

    public function getQuantiteActuelleAttribute(): int
    {
        return $this->quantite_initiale - $this->quantite_transferee;
    }
    
    public function getTotalVerseAttribute(): float
    {
        return $this->montant_paye ?? 0;
    }
    
    public function getRemiseTotaleAttribute(): float
    {
        $montantAttendu = 0;
        $reglements = $this->reglements()->with('details')->get();
        $orderItems = $this->items;

        foreach ($reglements as $reglement) {
            foreach ($reglement->details as $detail) {
                $orderItem = $orderItems->firstWhere('unite_de_vente_id', $detail->unite_de_vente_id);
                if ($orderItem) {
                    $montantAttendu += $detail->quantite_vendue * $orderItem->prix_unitaire;
                }
            }
        }
        
        if ($montantAttendu === 0.0) return 0.0;
        return $this->total_verse - $montantAttendu;
    }
}