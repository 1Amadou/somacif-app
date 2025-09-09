<?php

namespace App\Filament\Resources\ReglementResource\Pages;

use App\Filament\Resources\ReglementResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateReglement extends CreateRecord
{
    protected static string $resource = ReglementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }

    // --- CORRECTION DÉFINITIVE ---
    // La méthode afterCreate() est entièrement supprimée.
    // L'observateur est maintenant la seule source de vérité pour la logique
    // post-création, ce qui élimine les conflits et les doubles exécutions.
    
    protected function getRedirectUrl(): string
    {
        // On peut rediriger vers la vue du règlement nouvellement créé
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}