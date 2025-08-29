<?php

namespace App\Filament\Resources\ReglementResource\Pages;

use App\Filament\Resources\ReglementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReglement extends EditRecord
{
    protected static string $resource = ReglementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
