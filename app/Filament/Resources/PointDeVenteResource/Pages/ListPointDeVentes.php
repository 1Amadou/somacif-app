<?php

namespace App\Filament\Resources\PointDeVenteResource\Pages;

use App\Filament\Resources\PointDeVenteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPointDeVentes extends ListRecords
{
    protected static string $resource = PointDeVenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
