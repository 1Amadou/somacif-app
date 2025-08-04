<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PointDeVenteResource\Pages;
use App\Models\PointDeVente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PointDeVenteResource extends Resource
{
    protected static ?string $model = PointDeVente::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Points de Vente';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')->required()->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->options([
                        'Principal' => 'Principal',
                        'Secondaire' => 'Secondaire',
                        'Partenaire' => 'Partenaire',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('adresse')->required(),
                Forms\Components\TextInput::make('telephone'),
                Forms\Components\TextInput::make('horaires'),
                Forms\Components\TextInput::make('Maps_link')
                    ->label('Lien Google Maps')
                    ->url()
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('adresse'),
                Tables\Columns\TextColumn::make('telephone'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPointDeVentes::route('/'),
            'create' => Pages\CreatePointDeVente::route('/create'),
            'edit' => Pages\EditPointDeVente::route('/{record}/edit'),
        ];
    }
}