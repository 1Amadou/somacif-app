<?php

namespace App\Filament\Resources\VenteDirecteResource\Pages;

use App\Filament\Resources\VenteDirecteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVenteDirecte extends EditRecord
{
    protected static string $resource = VenteDirecteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
