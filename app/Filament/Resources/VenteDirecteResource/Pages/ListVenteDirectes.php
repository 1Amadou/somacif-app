<?php

namespace App\Filament\Resources\VenteDirecteResource\Pages;

use App\Filament\Resources\VenteDirecteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVenteDirectes extends ListRecords
{
    protected static string $resource = VenteDirecteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
