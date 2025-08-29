<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    /**
     * Remplit le formulaire avec les données existantes de la commande.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // On s'assure que les articles sont chargés depuis la relation 'items'
        $itemsData = [];
        foreach ($this->getRecord()->items as $item) {
            $itemsData[] = [
                'unite_de_vente_id' => $item->unite_de_vente_id,
                'quantite' => $item->quantite,
                'prix_unitaire' => $item->prix_unitaire,
            ];
        }

        $data['items'] = $itemsData;
        return $data;
    }

    /**
     * Sauvegarde la commande et ses articles de manière plus simple et robuste.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // On sépare les données de la commande principale des articles
        $itemsData = $data['items'];
        unset($data['items']);

        // Mettre à jour la commande principale
        $record->update($data);

        // Supprimer tous les anciens articles et les recréer avec les nouvelles données
        $record->items()->delete();
        $record->items()->createMany($itemsData);

        return $record;
    }
}