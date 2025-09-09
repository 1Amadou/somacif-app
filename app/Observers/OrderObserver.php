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
     * Gère la validation du stock AVANT la création d'une commande.
     * Si une commande est créée directement avec le statut "Validée", cette méthode
     * s'assure que le stock est disponible dans l'entrepôt principal.
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
     * C'est la correction clé pour le module de Vente Directe.
     */
    public function created(Order $order): void
    {
        // Si la commande est créée directement avec le statut "Validée",
        // on déclenche immédiatement le transfert de stock.
        if ($order->statut === OrderStatusEnum::VALIDEE) {
            DB::transaction(function () use ($order) {
                $this->transfertStockFromEntrepôtToPointDeVente($order);
            });
        }
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
                // Si on valide une commande qui était en attente (flux standard)
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
     * Valide que le stock est suffisant dans l'entrepôt principal pour le transfert.
     * Lance une exception si le stock est insuffisant.
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
    
    /**
     * Exécute le mouvement de stock : décrémente l'entrepôt et incrémente le point de vente.
     */
    protected function transfertStockFromEntrepôtToPointDeVente(Order $order): void
    {
        $entrepotId = $this->getEntrepôtPrincipalId();
        $pointDeVenteLieuId = $order->pointDeVente?->lieuDeStockage?->id;

        if (!$entrepotId || !$pointDeVenteLieuId) {
            throw new Exception("Lieu de stockage source ou destination manquant.");
        }

        foreach ($order->items as $item) {
            // Décrémenter le stock de l'entrepôt
            Inventory::where('lieu_de_stockage_id', $entrepotId)
                ->where('unite_de_vente_id', $item->unite_de_vente_id)
                ->decrement('quantite_stock', $item->quantite);

            // Incrémenter le stock du point de vente (le crée s'il n'existe pas)
            Inventory::firstOrCreate(
                ['lieu_de_stockage_id' => $pointDeVenteLieuId, 'unite_de_vente_id' => $item->unite_de_vente_id],
                ['quantite_stock' => 0]
            )->increment('quantite_stock', $item->quantite);
        }
    }

    /**
     * Inverse le mouvement de stock en cas d'annulation de commande.
     */
    protected function returnStockFromPointDeVenteToEntrepôt(Order $order): void
    {
        $entrepotId = $this->getEntrepôtPrincipalId();
        $pointDeVenteLieuId = $order->pointDeVente?->lieuDeStockage?->id;

        if (!$entrepotId || !$pointDeVenteLieuId) {
            throw new Exception("Impossible de retourner le stock : lieu source ou destination manquant.");
        }

        foreach ($order->items as $item) {
            // Décrémenter le stock du point de vente
            $inventairePointDeVente = Inventory::where('lieu_de_stockage_id', $pointDeVenteLieuId)
                ->where('unite_de_vente_id', $item->unite_de_vente_id)->first();
            
            if ($inventairePointDeVente) {
                $inventairePointDeVente->decrement('quantite_stock', $item->quantite);
            }

            // Ré-incrémenter le stock de l'entrepôt
            $inventaireEntrepôt = Inventory::where('lieu_de_stockage_id', $entrepotId)
                ->where('unite_de_vente_id', $item->unite_de_vente_id)->first();

            if ($inventaireEntrepôt) {
                $inventaireEntrepôt->increment('quantite_stock', $item->quantite);
            }
        }
    }

    /**
     * Gère l'envoi des notifications en fonction du nouveau statut de la commande.
     */
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

    /**
     * Récupère l'ID de l'entrepôt principal depuis le cache pour optimiser les performances.
     */
    private function getEntrepôtPrincipalId(): ?int
    {
        return cache()->rememberForever('entrepot_principal_id', function () {
            return LieuDeStockage::where('type', 'entrepot')->value('id');
        });
    }
}