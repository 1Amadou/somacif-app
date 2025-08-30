<?php

namespace App\Filament\Resources\ArrivageResource\Pages;

use App\Filament\Resources\ArrivageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewArrivage extends ViewRecord
{
    protected static string $resource = ArrivageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}