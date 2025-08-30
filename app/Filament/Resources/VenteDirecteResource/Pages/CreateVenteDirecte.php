<?php

namespace App\Filament\Resources\VenteDirecteResource\Pages;

use App\Filament\Resources\VenteDirecteResource;
use App\Models\Client;
use App\Models\PointDeVente;
use App\Models\Reglement;
use App\Observers\ReglementObserver;
use App\Services\StockManager;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateVenteDirecte extends CreateRecord
{
    protected static string $resource = VenteDirecteResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            
            // --- LOGIQUE AMÉLIORÉE POUR LE CLIENT ---
            $clientId = $data['client_id'];

            // Si aucun client n'est sélectionné, on utilise notre client par défaut.
            if (empty($clientId)) {
                $clientComptoir = Client::where('email', 'comptoir@somacif.net')->firstOrFail();
                $clientId = $clientComptoir->id;
            }
            // -----------------------------------------

            // On récupère le point de vente qui représente notre entrepôt.
            $pointDeVenteEntrepot = PointDeVente::where('nom', 'Entrepôt Principal (Ventes Directes)')->firstOrFail();

            // 1. Préparer les données pour la Commande (Order)
            $orderData = [
                'client_id' => $clientId, // Utilise le bon ID client
                'point_de_vente_id' => $pointDeVenteEntrepot->id, // Toujours notre entrepôt
                'numero_commande' => 'VD-' . random_int(1000, 9999),
                'statut' => 'validee',
                'montant_total' => $data['montant_total'],
                'notes' => $data['notes'],
                'statut_paiement' => 'non_payee',
                'montant_paye' => 0,
                'is_vente_directe' => true,
            ];
            
            // 2. Créer la Commande (déclenche l'OrderObserver)
            $order = static::getModel()::create($orderData);
            $order->items()->createMany($data['items']);

            // 3. Préparer les données pour le Règlement
            $reglementData = [
                'client_id' => $clientId, // Utilise le bon ID client
                'date_reglement' => $data['date_vente'],
                'montant_verse' => $data['montant_total'],
                'montant_calcule' => $data['montant_total'],
                'methode_paiement' => $data['methode_paiement'],
                'user_id' => auth()->id(),
            ];

            // 4. Créer le Règlement et ses détails
            $reglement = Reglement::create($reglementData);
            $details = collect($data['items'])->map(fn ($item) => [
                'unite_de_vente_id' => $item['unite_de_vente_id'],
                'quantite_vendue' => $item['quantite'],
                'prix_de_vente_unitaire' => $item['prix_unitaire'],
            ]);
            $reglement->details()->createMany($details->all());
            $reglement->orders()->attach($order->id);

            // 5. Déclencher la logique de règlement
            (new ReglementObserver(new StockManager()))->process($reglement);

            return $order;
        });
    }
}