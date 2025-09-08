<?php

namespace App\Filament\Resources\ArrivageResource\Pages;

use App\Filament\Resources\ArrivageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArrivage extends EditRecord
{
    protected static string $resource = ArrivageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // La méthode mutateFormDataBeforeSave() n'est plus nécessaire ici
    // car le user_id ne fait pas partie du formulaire d'édition
    // et ne risque donc pas d'être écrasé.
}