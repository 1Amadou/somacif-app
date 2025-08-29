<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\PointDeVenteResource;

class PointsDeVenteRelationManager extends RelationManager
{
    protected static string $relationship = 'pointsDeVente';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')->options(['Principal' => 'Principal', 'Secondaire' => 'Secondaire', 'Partenaire' => 'Partenaire'])->required(),
                Forms\Components\TextInput::make('adresse'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nom')
            ->columns([
                Tables\Columns\TextColumn::make('nom')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('adresse')->searchable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('view_details')
                    ->label('Gérer le Point de Vente')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record): string => PointDeVenteResource::getUrl('edit', ['record' => $record]))
            ]);
    }
}