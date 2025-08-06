<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Livreur;
use App\Models\Order;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification; // Assurez-vous que cette ligne est présente
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('assignLivreur')
                ->label('Assigner un Livreur')
                ->icon('heroicon-o-truck')
                ->form([
                    Forms\Components\Select::make('livreurId')
                        ->label('Choisir un livreur')
                        ->options(Livreur::all()->pluck('name', 'id'))
                        ->required(),
                ])
                ->action(function (Order $record, array $data): void {
                    $record->livreur_id = $data['livreurId'];
                    $record->statut = 'En cours de livraison';
                    $record->save();
                    
                    // On envoie une notification de succès
                    Notification::make()
                        ->title('Livreur assigné avec succès')
                        ->success()
                        ->send();
                })
                ->visible(fn (Order $record) => $record->statut !== 'Livrée' && $record->statut !== 'Annulée'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return static::getResource()::infolist($infolist);
    }
}