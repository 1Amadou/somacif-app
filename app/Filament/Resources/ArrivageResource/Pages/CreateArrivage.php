<?php

namespace App\Filament\Resources\ArrivageResource\Pages;

use App\Filament\Resources\ArrivageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateArrivage extends CreateRecord
{
    protected static string $resource = ArrivageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Cette méthode s'assure que l'ID de l'utilisateur est bien ajouté
    // avant que les données ne soient enregistrées.
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }
    
    // Nous n'avons plus besoin de la méthode handleRecordCreation()
    // car la structure des données du formulaire correspond maintenant
    // parfaitement à la structure de la table `arrivages`.
}