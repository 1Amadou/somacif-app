<?php

namespace App\Filament\Resources\VenteDirecteResource\Pages;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\VenteDirecteResource;
use App\Models\Order;
use App\Models\VenteDirecte;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateVenteDirecte extends CreateRecord
{
    protected static string $resource = VenteDirecteResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // 1. Créer la Commande avec le statut 'VALIDEE'
            // L'OrderObserver va maintenant se déclencher et gérer le transfert de stock
            // de l'entrepôt vers le point de vente.
            $order = Order::create([
                'client_id' => $data['client_id'],
                'point_de_vente_id' => $data['point_de_vente_id'],
                'numero_commande' => $data['numero_facture'],
                'statut' => OrderStatusEnum::VALIDEE, // CORRECTION : Le statut correct
                'montant_total' => $data['montant_total'],
                'notes' => $data['notes'],
                'is_vente_directe' => true,
                'statut_paiement' => PaymentStatusEnum::COMPLETEMENT_REGLE,
                'montant_paye' => $data['montant_total'],
                'user_id' => auth()->id(),
            ]);

            // 2. Attacher les articles à la commande
            $order->items()->createMany($data['items']);

            // 3. Créer le Règlement associé
            // Le ReglementObserver va se déclencher et déstocker le point de vente.
            $reglement = $order->reglements()->create([
                'client_id' => $data['client_id'],
                'date_reglement' => $data['date_vente'],
                'montant_verse' => $data['montant_total'],
                'montant_calcule' => $data['montant_total'],
                'methode_paiement' => $data['methode_paiement'],
                'user_id' => auth()->id(),
            ]);

            // 4. Mapper et attacher les détails au règlement
            $detailsData = collect($data['items'])->map(fn ($item) => [
                'unite_de_vente_id' => $item['unite_de_vente_id'],
                'quantite_vendue' => $item['quantite'],
                'prix_de_vente_unitaire' => $item['prix_unitaire'],
            ])->all();
            $reglement->details()->createMany($detailsData);
            
            // On retourne la nouvelle commande pour la redirection
            return $order;
        });
    }
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Vente Directe Enregistrée')
            ->body('La commande et le règlement unifiés ont été créés.');
    }

    protected function getRedirectUrl(): string
    {
        // On redirige l'utilisateur vers la NOUVELLE COMMANDE qu'il vient de créer
        return OrderResource::getUrl('view', ['record' => $this->record]);
    }
}