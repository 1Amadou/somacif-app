<?php

namespace App\Filament\Resources\ReglementResource\Pages;

use App\Filament\Resources\ReglementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReglements extends ListRecords
{
    protected static string $resource = ReglementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
