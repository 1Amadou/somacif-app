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

    public function annulerVente(): void
    {
        if ($this->statut === 'annulee') {
            return;
        }

        DB::transaction(function () {
            $stockManager = app(StockManager::class);
            $this->load('items.uniteDeVente', 'pointDeVente');
            
            // On s'assure que le stock n'est pas déjà dans l'entrepôt
            if ($this->statut === 'validee' || $this->statut === 'en_preparation' || $this->statut === 'en_cours_livraison') {
                foreach ($this->items as $item) {
                    // Le stock a été transféré vers un point de vente. On doit le récupérer.
                    $stockManager->decreaseInventoryStock($item->uniteDeVente, $item->quantite, $this->pointDeVente);
                    $stockManager->increaseInventoryStock($item->uniteDeVente, $item->quantite, null);
                }
            } elseif ($this->statut === 'livree') {
                // Dans le cas d'une annulation après livraison, une décision métier est nécessaire :
                // On remet le stock dans le point de vente ou pas ? On ne le remet pas dans l'entrepôt principal.
                // Par prudence, on ne fait rien pour le stock ici, car il est "chez le client".
            }

            // Mettre à jour les statuts de la commande
            $this->statut = 'annulee';
            $this->statut_paiement = 'Annulé'; // ou un statut plus spécifique comme 'annulee_sans_remboursement'
            $this->save();
        });
    }

    public function confirmReception(): void
    {
        if ($this->statut === 'en_cours_livraison') {
            $this->statut = 'livree';
            $this->client_confirmed_at = now();
            $this->save();
        }
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->numero_commande)) {
                $order->numero_commande = 'CMD-' . date('Ymd') . '-' . strtoupper(uniqid());
            }
        });
    }
}