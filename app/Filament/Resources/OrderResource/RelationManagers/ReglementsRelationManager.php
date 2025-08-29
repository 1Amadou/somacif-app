<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReglementsRelationManager extends RelationManager
{
    protected static string $relationship = 'client.reglements'; // On va chercher les règlements via le client

    public function form(Form $form): Form
    {
        return $form->schema([]); // On ne peut pas créer de règlement depuis ici
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('date_reglement')
            ->columns([
                Tables\Columns\TextColumn::make('date_reglement')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('montant_verse')->money('cfa'),
                Tables\Columns\TextColumn::make('montant_calcule')->money('cfa'),
            ])
            ->headerActions([
                // On pourrait ajouter un bouton qui redirige vers la page de création de règlement
            ]);
    }
}