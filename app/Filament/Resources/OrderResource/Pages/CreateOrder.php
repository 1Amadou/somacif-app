<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $total = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $total += ($item['quantite'] ?? 0) * ($item['prix_unitaire'] ?? 0);
            }
        }
        $data['montant_total'] = $total;
        $data['statut_paiement'] = 'non_paye';

        if (empty($data['numero_commande'])) {
            $data['numero_commande'] = 'CMD-' . Str::upper(Str::random(8));
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $itemsData = $data['items'];
        unset($data['items']);

        $order = static::getModel()::create($data);

        $order->items()->createMany($itemsData);

        return $order;
    }
}
