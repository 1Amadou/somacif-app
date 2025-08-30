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
    protected static ?string $title = 'Points de Vente Associés';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options(['Principal' => 'Principal', 'Secondaire' => 'Secondaire', 'Partenaire' => 'Partenaire'])
                    ->required(),
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
                Tables\Actions\CreateAction::make()
                    ->label('Ajouter un Point de Vente'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // ACTION CLÉ : Accès direct à la page de gestion complète du point de vente.
                Tables\Actions\Action::make('view_details')
                    ->label('Gérer et Voir le Stock')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('secondary')
                    ->url(fn ($record): string => PointDeVenteResource::getUrl('view', ['record' => $record])),
            ]);
    }
}