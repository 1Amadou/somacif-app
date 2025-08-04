<?php

namespace App\Filament\Resources\PointDeVenteResource\Pages;

use App\Filament\Resources\PointDeVenteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPointDeVente extends EditRecord
{
    protected static string $resource = PointDeVenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
