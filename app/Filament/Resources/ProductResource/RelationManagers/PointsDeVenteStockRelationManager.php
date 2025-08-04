<?php
namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PointsDeVenteStockRelationManager extends RelationManager
{
    protected static string $relationship = 'pointsDeVenteStock';
    protected static ?string $recordTitleAttribute = 'nom';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Ce formulaire est utilisé pour modifier une ligne existante
                Forms\Components\TextInput::make('quantite_stock')
                    ->label('Quantité en Stock')
                    ->required()
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Gestion du Stock par Point de Vente')
            ->columns([
                Tables\Columns\TextColumn::make('nom')->label('Point de Vente'),
                Tables\Columns\TextInputColumn::make('quantite_stock')->label('Stock (cartons)')->rules(['required', 'numeric']),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('quantite_stock')->required()->numeric(),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ]);
    }
}