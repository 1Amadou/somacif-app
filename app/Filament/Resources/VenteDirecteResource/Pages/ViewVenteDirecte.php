<?php

namespace App\Filament\Resources\VenteDirecteResource\Pages;

use App\Filament\Resources\VenteDirecteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVenteDirecte extends ViewRecord
{
    protected static string $resource = VenteDirecteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // On ne met pas de bouton "Modifier" pour une vente directe car elle est considérée comme finalisée.
        ];
    }
}