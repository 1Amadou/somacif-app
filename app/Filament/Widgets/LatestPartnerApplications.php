<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPartnerApplications extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Client::where('status', 'pending')->latest()
            )
            ->heading('Dernières Demandes de Partenariat')
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom de l\'entreprise'),
                Tables\Columns\TextColumn::make('telephone'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date de la demande')
                    ->date('d/m/Y'),
            ])
            ->actions([
                Tables\Actions\Action::make('Voir')
                    ->url(fn (Client $record): string => ClientResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}