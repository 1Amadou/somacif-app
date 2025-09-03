<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // On s'assure que la clé 'items' existe et est un tableau
        if (!isset($data['items']) || !is_array($data['items'])) {
            $data['items'] = [];
        }

        // Calcule le montant total en iterant sur les items
        $total = 0;
        foreach ($data['items'] as $item) {
            $total += ($item['quantite'] ?? 0) * ($item['prix_unitaire'] ?? 0);
        }

        // Ajoute les données calculées et l'ID de l'utilisateur
        $data['montant_total'] = $total;
        $data['statut_paiement'] = 'non_paye';
        $data['user_id'] = Auth::id();

        // Gère la génération du numéro de commande si le champ est vide
        if (empty($data['numero_commande'])) {
            $data['numero_commande'] = 'CMD-' . Str::upper(Str::random(8));
        }

        return $data;
    }
}