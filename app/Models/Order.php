<?php

namespace App\Models;

use App\Services\StockManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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
            'is_vente_directe' => 'boolean',
            'client_confirmed_at' => 'datetime',
            'livreur_confirmed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function pointDeVente(): BelongsTo { return $this->belongsTo(PointDeVente::class); }
    public function livreur(): BelongsTo { return $this->belongsTo(Livreur::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function reglements(): BelongsToMany { return $this->belongsToMany(Reglement::class, 'order_reglement', 'order_id', 'reglement_id'); }
    
    public function updatePaymentStatus(): void
    {
        $this->load('reglements');
        $totalPaye = $this->reglements->sum('montant_verse');
        $this->montant_paye = $totalPaye;

        if ($totalPaye >= $this->montant_total) {
            $this->statut_paiement = 'Complètement réglé';
        } elseif ($totalPaye > 0) {
            $this->statut_paiement = 'Partiellement réglé';
        } else {
            $this->statut_paiement = 'non_payee';
        }
        $this->saveQuietly();
    }

    /**
     * NOUVELLE LOGIQUE : Annule une vente et remet les produits en stock.
     */
    public function annulerVente(): void
    {
        if ($this->statut === 'annulee') {
            return; // Déjà annulée, on ne fait rien.
        }

        DB::transaction(function () {
            $stockManager = app(StockManager::class); // On récupère le StockManager proprement
            $this->load('items.uniteDeVente');

            // On identifie d'où le stock a été pris pour le remettre au bon endroit.
            if ($this->is_vente_directe || !$this->pointDeVente) {
                // Pour une vente directe, le stock a été pris du stock principal. On le remet.
                foreach ($this->items as $item) {
                    $stockManager->increaseMainStock($item->uniteDeVente, $item->quantite);
                }
            } else {
                // Pour une vente standard, le stock a été pris de l'inventaire du point de vente.
                // NOTE : La logique de remboursement du stock client peut être plus complexe.
                // Pour l'instant, nous nous concentrons sur la Vente Directe.
            }

            // Mettre à jour les statuts de la commande
            $this->statut = 'annulee';
            $this->statut_paiement = 'Annulé';
            $this->save();
        });
    }
}