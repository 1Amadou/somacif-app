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
    protected static ?string $title = 'Unités de Vente (Calibres, Conditionnements)';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom_unite')
                    ->label('Nom de l\'unité (ex: Carton, Sachet)')
                    ->required()->maxLength(255),
                
                Forms\Components\TextInput::make('calibre')
                    ->label('Calibre (ex: Moyen, 100-200g)')
                    ->required()->maxLength(255),

                // Le stock reste non-éditable, ce qui est correct
                Forms\Components\TextInput::make('stock_principal')
                    ->label('Stock Principal Actuel')
                    ->numeric()
                    ->readOnly()
                    ->helperText('Le stock est mis à jour automatiquement par les arrivages.'),

                Forms\Components\Fieldset::make('Grille Tarifaire (FCFA)')
                    ->schema([
                        Forms\Components\TextInput::make('prix_particulier')
                            ->label('Prix Particulier')
                            ->required()->numeric(),
                        Forms\Components\TextInput::make('prix_grossiste')
                            ->label('Prix Grossiste')
                            ->numeric()->nullable(),
                        Forms\Components\TextInput::make('prix_hotel_restaurant')
                            ->label('Prix Hôtel/Restaurant')
                            ->numeric()->nullable(),
                    ])->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // CORRECTION : On utilise `nom_complet` pour le titre du record
            ->recordTitleAttribute('nom_complet')
            ->columns([
                // CORRECTION : On affiche le nom de l'unité et le calibre
                Tables\Columns\TextColumn::make('nom_unite')->label('Unité'),
                Tables\Columns\TextColumn::make('calibre'),

                Tables\Columns\TextColumn::make('stock_principal')
                    ->label('Stock Principal')
                    ->badge()->color('primary'),
                
                Tables\Columns\TextColumn::make('prix_particulier')
                    ->label('Prix Particulier')->money('XOF'),
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