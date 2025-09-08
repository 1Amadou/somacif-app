<?php

namespace App\Filament\Resources\StockTransfertResource\Pages;

use App\Filament\Resources\StockTransfertResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockTransferts extends ListRecords
{
    protected static string $resource = StockTransfertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
