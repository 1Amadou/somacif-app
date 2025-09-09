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
            // Action pour imprimer le reçu, qui utilise la logique de comptage et de numéro de versement
            Actions\Action::make('print_receipt')
                ->label('Imprimer le Reçu')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => route('invoices.reglement-receipt', $this->getRecord()))
                ->openUrlInNewTab(),
            
            Actions\EditAction::make(),
        ];
    }
}