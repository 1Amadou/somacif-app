<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UniteDeVentesRelationManager extends RelationManager
{
    protected static string $relationship = 'uniteDeVentes';
    protected static ?string $recordTitleAttribute = 'nom_unite';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('calibre')
                    ->label('Calibre (ex: M, G, P)')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nom_unite')
                    ->label('Nom de l\'unité (ex: Carton 10kg)')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('prix_grossiste')
                    ->label('Prix Grossiste')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('prix_hotel_restaurant')
                    ->label('Prix Hôtel/Restaurant')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('prix_particulier')
                    ->label('Prix Particulier')
                    ->numeric()
                    ->required(),
                // CORRECTION : On remet 'prix_unitaire'
                Forms\Components\TextInput::make('prix_unitaire')
                    ->label('Prix de Vente Interne')
                    ->numeric()
                    ->required()
                    ->helperText('Prix utilisé par défaut dans le système de commande interne.'),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nom_unite')
            ->columns([
                Tables\Columns\TextColumn::make('calibre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nom_unite')->searchable(),
                // CORRECTION : On remet 'prix_unitaire'
                Tables\Columns\TextColumn::make('prix_unitaire')->label('Prix Interne')->money('cfa')->sortable(),
                Tables\Columns\TextColumn::make('stock')->sortable(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}