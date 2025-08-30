<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Filament\Resources\ReglementResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReglementsRelationManager extends RelationManager
{
    protected static string $relationship = 'reglements';
    protected static ?string $title = 'Historique des Paiements Effectués';

    public function form(Form $form): Form
    {
        return $form->schema([]); // Pas de formulaire de création ici
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('date_reglement')
                    ->label('Date du Paiement')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('montant_verse')
                    ->label('Montant Versé')
                    ->money('XOF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('methode_paiement')
                    ->label('Méthode')
                    ->badge(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Enregistré par'),
            ])
            ->actions([
                // CORRECTION : Utilisation de la bonne classe "Action"
                Tables\Actions\Action::make('view_reglement')
                    ->label('Voir le Détail')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record): string => ReglementResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ]);
    }

    /**
     * Assure que cette table est en lecture seule.
     */
    public function isReadOnly(): bool
    {
        return true;
    }
}