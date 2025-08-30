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

    protected function mutateFormDataBeforeFill(array $data): array
    {
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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $itemsData = $data['items'];
        unset($data['items']);

        $record->update($data);

        // Supprimer les anciens items et recréer pour simplifier la logique
        $record->items()->delete();
        $record->items()->createMany($itemsData);

        return $record;
    }
}
