<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
             // Assurez-vous que le montant payé est initialisé à 0
            if (is_null($order->montant_paye)) {
                $order->montant_paye = 0;
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
    Log::info("--- DÉBUT Order::updatePaymentStatus pour la commande ID: {$this->id} ---");
    
    $totalVerse = $this->reglements()->sum('montant_verse');
    $this->montant_paye = $totalVerse;

    Log::info("Calculs pour commande {$this->id}: Montant Total Proforma = {$this->montant_total}, Total Versé = {$totalVerse}");

    $originalStatus = $this->statut_paiement->value;

    if ($this->montant_total > 0 && $totalVerse >= $this->montant_total) {
        $this->statut_paiement = PaymentStatusEnum::COMPLETEMENT_REGLE;
    } elseif ($totalVerse > 0) {
        $this->statut_paiement = PaymentStatusEnum::PARTIELLEMENT_REGLE;
    } else {
        $this->statut_paiement = PaymentStatusEnum::NON_PAYEE;
    }
    
    Log::info("Statut calculé: {$this->statut_paiement->value}. (Ancien statut: {$originalStatus})");

    $this->saveQuietly();
    Log::info("--- FIN Order::updatePaymentStatus --- Commande sauvegardée.");
}
    
    public function recalculateTotal(): void
    {
        $this->loadMissing('items');
        $total = $this->items->sum(fn ($item) => $item->quantite * $item->prix_unitaire);
        $this->update(['montant_total' => $total]);
    }

    // --- ACCESSEURS (GETTERS) POUR L'AFFICHAGE ---
    public function getQuantiteActuelleAttribute(): int
    {
        return $this->items()->sum('quantite');
    }

    public function getQuantiteTransfereeAttribute(): int
    {
        // On charge d'abord la collection avec ->get()
        $transferts = $this->transfertsOrigine()->get();
        
        // Ensuite, on travaille sur la collection PHP
        return $transferts->sum(function ($transfert) {
            // Le casting en 'array' est une sécurité supplémentaire
            return collect((array) $transfert->details)->sum('quantite');
        });
    }

    public function getQuantiteInitialeAttribute(): int
    {
        return $this->quantite_actuelle + $this->quantite_transferee;
    }

    public function getQuantiteRegleeAttribute(): int
    {
        return $this->reglements()->with('details')->get()->sum(fn ($reglement) => $reglement->details->sum('quantite_vendue'));
    }
    
    public function getQuantiteRestanteAPayerAttribute(): int
    {
        $restant = $this->quantite_actuelle - $this->quantite_reglee;
        return max(0, $restant);
    }
    
    public function getTotalVerseAttribute(): float
    {
        return $this->montant_paye ?? 0;
    }
    
    public function getSoldeRestantAPayerAttribute(): float
    {
        return $this->montant_total - $this->total_verse;
    }
    
    public function getRemiseTotaleAttribute(): float
    {
        $montantAttendu = 0;
        // On charge les relations nécessaires pour le calcul
        $reglements = $this->reglements()->with('details')->get();
        $orderItems = $this->items()->get()->keyBy('unite_de_vente_id');

        foreach ($reglements as $reglement) {
            foreach ($reglement->details as $detail) {
                // On trouve l'article de commande correspondant
                $orderItem = $orderItems->get($detail->unite_de_vente_id);
                if ($orderItem) {
                    // Le "montant attendu" est basé sur le prix initial de la commande
                    $montantAttendu += $detail->quantite_vendue * $orderItem->prix_unitaire;
                }
            }
        }
        
        if ($montantAttendu === 0.0) return 0.0;

        // Le total versé est le montant réel payé
        $totalVerse = $this->total_verse;

        // La "marge" est la différence.
        // Négatif = vendu moins cher (remise), Positif = vendu plus cher (surprix)
        return $totalVerse - $montantAttendu;
    }
}