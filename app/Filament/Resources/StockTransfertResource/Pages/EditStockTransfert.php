<?php

namespace App\Filament\Resources\StockTransfertResource\Pages;

use App\Filament\Resources\StockTransfertResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockTransfert extends EditRecord
{
    protected static string $resource = StockTransfertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
