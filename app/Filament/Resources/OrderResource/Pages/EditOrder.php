<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ViewAction::make(),
        ];
    }

    /**
     * CORRECTION : C'est la méthode correcte dans Filament pour modifier les données
     * juste avant la sauvegarde.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 1. On récupère la liste des articles depuis le formulaire.
        $items = $data['items'] ?? [];
        
        // 2. On calcule le nouveau montant total.
        $total = collect($items)->sum(function ($item) {
            return ($item['quantite'] ?? 0) * ($item['prix_unitaire'] ?? 0);
        });

        // 3. On met à jour le montant_total dans les données qui seront sauvegardées.
        $data['montant_total'] = $total;

        return $data;
    }
}