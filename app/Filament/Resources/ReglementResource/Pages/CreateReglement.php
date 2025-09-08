<?php

namespace App\Filament\Resources\ReglementResource\Pages;

use App\Filament\Resources\ReglementResource;
use App\Models\Inventory;
use App\Models\LieuDeStockage;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CreateReglement extends CreateRecord
{
    protected static string $resource = ReglementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }

    protected function afterCreate(): void
    {
        $reglement = $this->getRecord();
        // On recharge TOUT ce dont on a besoin pour être sûr d'avoir les données fraîches.
        $reglement->load('order.pointDeVente.lieuDeStockage', 'details.uniteDeVente');

        Log::info('--- DÉBUT DU TRAITEMENT POST-RÈGLEMENT ---');

        try {
            DB::transaction(function () use ($reglement) {
                $lieuDeStockage = $reglement->order?->pointDeVente?->lieuDeStockage;
                if (!$lieuDeStockage) {
                    throw new Exception("Lieu de stockage introuvable.");
                }

                // 1. Déstockage final
                Log::info('[afterCreate] Début du déstockage pour ' . $reglement->details->count() . ' article(s).');
                foreach ($reglement->details as $detail) {
                    $inventory = Inventory::where('lieu_de_stockage_id', $lieuDeStockage->id)
                                        ->where('unite_de_vente_id', $detail->unite_de_vente_id)
                                        ->firstOrFail();
                    
                    $inventory->decrement('quantite_stock', $detail->quantite_vendue);
                }
                Log::info('[afterCreate] Déstockage terminé.');

                // 2. Mise à jour du statut de paiement
                Log::info('[afterCreate] Mise à jour du statut de paiement...');
                $order = $reglement->order;
                if ($order) {
                    // *** LA CORRECTION CRUCIALE EST ICI ***
                    // On force le rechargement de la relation pour qu'elle inclue ce nouveau règlement
                    $order->load('reglements'); 
                    $order->updatePaymentStatus();
                }
                Log::info('[afterCreate] Statut de paiement mis à jour.');
            });

        } catch (Exception $e) {
            Log::error("[afterCreate] ÉCHEC du traitement post-règlement : " . $e->getMessage());
            throw $e;
        }
        
        Log::info('--- TRAITEMENT POST-RÈGLEMENT TERMINÉ ---');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}