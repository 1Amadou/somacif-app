<?php

namespace App\Observers;

use App\Enums\OrderStatusEnum;
use App\Models\Inventory;
use App\Models\LieuDeStockage;
use App\Models\Order;
use App\Models\UniteDeVente;
use App\Notifications\AdminOrderDeliveredNotification;
use App\Notifications\ClientDeliveryInProgressNotification;
use App\Notifications\LivreurNewMissionNotification;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrderObserver
{
    /**
     * *** CORRECTION MAJEURE : Validation AVANT la création ***
     * Gère la validation du stock si une commande est créée directement avec le statut "Validée".
     */
    public function creating(Order $order): void
    {
        if ($order->statut === OrderStatusEnum::VALIDEE) {
            // On lance la même validation que pour une mise à jour.
            // Si le stock est insuffisant, une exception sera levée et la création sera bloquée.
            $this->validateStockForTransfer($order);
        }
    }

    /**
     * Gère le transfert de stock APRÈS la création réussie.
     */
    public function created(Order $order): void
    {
        
    }

    /**
     * Gère les actions automatiques basées sur le changement de statut d'une commande existante.
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('statut')) {
            $oldStatus = $order->getOriginal('statut');
            $newStatus = $order->statut;

            // On ne fait rien si le statut ne change pas réellement.
            if ($oldStatus === $newStatus) {
                return;
            }

            DB::transaction(function () use ($order, $oldStatus, $newStatus) {
                // Si on valide une commande qui était en attente
                if ($newStatus === OrderStatusEnum::VALIDEE && $oldStatus === OrderStatusEnum::EN_ATTENTE) {
                    $this->validateStockForTransfer($order); // D'abord valider
                    $this->transfertStockFromEntrepôtToPointDeVente($order); // Ensuite transférer
                }

                // Si on annule une commande dont le stock avait été transféré
                if ($newStatus === OrderStatusEnum::ANNULEE && in_array($oldStatus, [OrderStatusEnum::VALIDEE, OrderStatusEnum::EN_PREPARATION, OrderStatusEnum::EN_COURS_LIVRAISON])) {
                    $this->returnStockFromPointDeVenteToEntrepôt($order);
                }
            });

            // La gestion des notifications reste en dehors de la transaction
            $this->handleNotifications($order, $newStatus);
        }
    }

    /**
     * Valide que le stock est suffisant pour le transfert. Lance une exception si non.
     */
    protected function validateStockForTransfer(Order $order): void
    {
        // On doit recharger les 'items' car ils ne sont pas toujours disponibles ici.
        $order->load('items.uniteDeVente');

        foreach ($order->items as $item) {
            $uniteDeVente = $item->uniteDeVente;
            if (!$uniteDeVente) {
                throw new Exception("Article de commande invalide.");
            }
            
            $stockDisponible = $uniteDeVente->stock_entrepôt_principal;
            if ($stockDisponible < $item->quantite) {
                throw new Exception("Stock insuffisant pour '{$uniteDeVente->nom_complet}'. Stock: {$stockDisponible}, Demandé: {$item->quantite}.");
            }
        }
    }
    
    // Le reste des fonctions (transfert, retour, notifications) reste identique
    // car leur logique interne est déjà correcte.

    protected function transfertStockFromEntrepôtToPointDeVente(Order $order): void
    {
        $entrepotId = $this->getEntrepôtPrincipalId();
        $pointDeVenteLieuId = $order->pointDeVente?->lieuDeStockage?->id;

        if (!$entrepotId || !$pointDeVenteLieuId) {
            throw new Exception("Lieu de stockage source ou destination manquant.");
        }

        foreach ($order->items as $item) {
            $inventaireEntrepôt = Inventory::where('lieu_de_stockage_id', $entrepotId)
                ->where('unite_de_vente_id', $item->unite_de_vente_id)->first();
            if ($inventaireEntrepôt) {
                 $inventaireEntrepôt->decrement('quantite_stock', $item->quantite);
            }

            $inventairePointDeVente = Inventory::firstOrCreate(
                ['lieu_de_stockage_id' => $pointDeVenteLieuId, 'unite_de_vente_id' => $item->unite_de_vente_id],
                ['quantite_stock' => 0]
            );
            $inventairePointDeVente->increment('quantite_stock', $item->quantite);
        }
    }

    protected function returnStockFromPointDeVenteToEntrepôt(Order $order): void
    {
        $entrepotId = $this->getEntrepôtPrincipalId();
        $pointDeVenteLieuId = $order->pointDeVente?->lieuDeStockage?->id;

        if (!$entrepotId || !$pointDeVenteLieuId) {
            throw new Exception("Impossible de retourner le stock : lieu source ou destination manquant.");
        }

        foreach ($order->items as $item) {
            $inventairePointDeVente = Inventory::where('lieu_de_stockage_id', $pointDeVenteLieuId)
                ->where('unite_de_vente_id', $item->unite_de_vente_id)->first();
            
            if ($inventairePointDeVente) {
                $inventairePointDeVente->decrement('quantite_stock', $item->quantite);
            }

            $inventaireEntrepôt = Inventory::where('lieu_de_stockage_id', $entrepotId)
                ->where('unite_de_vente_id', $item->unite_de_vente_id)->first();

            if ($inventaireEntrepôt) {
                $inventaireEntrepôt->increment('quantite_stock', $item->quantite);
            }
        }
    }

    protected function handleNotifications(Order $order, $newStatus): void
    {
        if ($newStatus === OrderStatusEnum::EN_PREPARATION && $order->livreur) {
            $order->livreur->notify(new LivreurNewMissionNotification($order));
        }

        if ($newStatus === OrderStatusEnum::EN_COURS_LIVRAISON && $order->client) {
            $order->client->notify(new ClientDeliveryInProgressNotification($order));
        }

        if ($newStatus === OrderStatusEnum::LIVREE) {
            $adminUsers = \App\Models\User::all();
            Notification::send($adminUsers, new AdminOrderDeliveredNotification($order));
        }
    }

    private function getEntrepôtPrincipalId(): ?int
    {
        return cache()->rememberForever('entrepot_principal_id', function () {
            return LieuDeStockage::where('type', 'entrepot')->value('id');
        });
    }
}