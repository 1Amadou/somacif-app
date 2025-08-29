<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReglementsRelationManager extends RelationManager
{
    protected static string $relationship = 'reglements';
    protected static ?string $title = 'Historique des Règlements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Le formulaire est vide car on ne crée pas de règlement depuis ici
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('date_reglement')->date('d/m/Y')->label('Date'),
                Tables\Columns\TextColumn::make('montant_verse')->money('cfa')->label('Montant Versé'),
                Tables\Columns\TextColumn::make('montant_calcule')->money('cfa')->label('Montant Calculé'),
                Tables\Columns\TextColumn::make('user.name')->label('Enregistré par'),
            ])
            ->defaultSort('date_reglement', 'desc');
    }
}