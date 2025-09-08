<?php

namespace App\Filament\Resources\StockTransfertResource\Pages;

use App\Filament\Resources\StockTransfertResource;
use App\Models\Inventory;
use App\Models\PointDeVente;
use Filament\Actions;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStockTransfert extends CreateRecord
{
    protected static string $resource = StockTransfertResource::class;

    // Cette méthode est appelée par notre vue pour charger le stock
    public function loadSourceInventory(int $sourceId): void
    {
        $inventory = Inventory::where('point_de_vente_id', $sourceId)
            ->where('quantite_stock', '>', 0)
            ->with('uniteDeVente.product')
            ->get()
            ->map(fn ($inv) => [
                'unite_de_vente_id' => $inv->unite_de_vente_id,
                'nom_complet' => $inv->uniteDeVente->nom_complet,
                'quantite_stock' => $inv->quantite_stock,
            ])
            ->sortBy('nom_complet')
            ->values();

        // On envoie les données à notre composant Alpine.js
        $this->dispatch('inventory-loaded', inventory: $inventory);
    }

    protected function getCreateFormAction(): Actions\Action
    {
        return Actions\Action::make('create')
            ->label('Préparer le Transfert')
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    // On utilise une action "after" pour la confirmation
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            Actions\Action::make('confirm_transfer')
                ->label('Enregistrer le Transfert')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Confirmer le Transfert')
                ->modalDescription(function (Get $get) {
                    // Crée le récapitulatif pour la boîte de dialogue
                    $data = $get();
                    $source = PointDeVente::find($data['source_point_de_vente_id'])?->nom ?? 'N/A';
                    $destination = $data['destination_point_de_vente_id'] === 'null' ? 'Entrepôt Principal' : PointDeVente::find($data['destination_point_de_vente_id'])?->nom;
                    $details = $data['details'] ?? [];
                    $count = count($details);
                    return "Vous êtes sur le point de transférer {$count} article(s) de **{$source}** vers **{$destination}**. Êtes-vous sûr de vouloir continuer ?";
                })
                ->action(fn () => $this->create()), // Appelle la méthode de création standard
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        if ($data['destination_point_de_vente_id'] === 'null') {
            $data['destination_point_de_vente_id'] = null;
        }

        // On nettoie les 'détails' pour ne garder que les articles avec une quantité > 0
        $data['details'] = collect($data['details'] ?? [])->filter(fn ($item) => $item['quantite'] > 0)->all();

        return $data;
    }
}