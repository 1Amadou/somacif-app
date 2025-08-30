<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UniteDeVentesRelationManager extends RelationManager
{
    protected static string $relationship = 'uniteDeVentes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom_unite')
                    ->label('Nom de l\'unité (ex: Carton, Sachet)')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('calibre')
                    ->label('Calibre (ex: Moyen, 100-200g)')
                    ->required()
                    ->maxLength(255),

                // CHAMP VERROUILLÉ
                Forms\Components\TextInput::make('stock')
                    ->label('Stock Principal Actuel')
                    ->numeric()
                    ->readOnly() // Le stock ne peut plus être modifié manuellement
                    ->helperText('Le stock est mis à jour automatiquement par les arrivages.'),

                Forms\Components\Fieldset::make('Grille Tarifaire')
                    ->schema([
                        Forms\Components\TextInput::make('prix_unitaire')
                            ->label('Prix Particulier')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('prix_grossiste')
                            ->label('Prix Grossiste')
                            ->numeric(),
                        Forms\Components\TextInput::make('prix_hotel_restaurant')
                            ->label('Prix Hôtel/Restaurant')
                            ->numeric(),
                    ])->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nom_unite')
            ->columns([
                Tables\Columns\TextColumn::make('nom_unite'),
                Tables\Columns\TextColumn::make('calibre'),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock Principal')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('prix_unitaire')
                    ->label('Prix Particulier'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}