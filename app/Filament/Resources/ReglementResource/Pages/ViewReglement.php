<?php
namespace App\Filament\Resources\ReglementResource\Pages;
use App\Filament\Resources\ReglementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
class ViewReglement extends ViewRecord
{
    protected static string $resource = ReglementResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}