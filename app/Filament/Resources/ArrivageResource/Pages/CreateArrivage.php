<?php

namespace App\Filament\Resources\ArrivageResource\Pages;

use App\Filament\Resources\ArrivageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Arrivage;
use App\Models\ArrivageDetail;

class CreateArrivage extends CreateRecord
{
    protected static string $resource = ArrivageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }
    
    protected function handleRecordCreation(array $data): Model
    {
        // 1. Sauvegarder les détails du répéteur et les retirer des données
        // principales pour éviter l'erreur de base de données.
        $details_produits = $data['details_produits'] ?? [];
        unset($data['details_produits']);

        // 2. Créer l'Arrivage en utilisant le tableau de données nettoyé.
        // On s'assure que user_id est présent.
        $data['user_id'] = Auth::id();
        $arrivage = Arrivage::create($data);

        // 3. Créer les détails de l'arrivage en utilisant la variable temporaire.
        foreach ($details_produits as $detailData) {
            $arrivage->details()->create([
                'unite_de_vente_id' => $detailData['unite_de_vente_id'],
                'quantite' => $detailData['quantite'],
                'prix_achat_unitaire' => $detailData['prix_achat_unitaire'],
            ]);
        }

        return $arrivage;
    }
}