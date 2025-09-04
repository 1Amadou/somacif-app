<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Partenaires';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Générales')
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->options([
                                'Grossiste' => 'Grossiste',
                                'Hôtel/Restaurant' => 'Hôtel/Restaurant',
                                'Particulier' => 'Particulier',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('telephone')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true), // Ajout de la validation unique
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true) // Ajout de la validation unique
                            ->nullable(), // Permet de ne pas avoir d'email
                    ])->columns(2),

                Forms\Components\Section::make('Informations de Connexion (Portail Client)')
                    ->schema([
                        Forms\Components\TextInput::make('identifiant_unique_somacif')
                            ->label('Identifiant Unique')
                            ->disabled()
                            ->dehydrated(fn ($state) => filled($state))
                            ->default(fn () => 'SOM-' . Str::upper(Str::random(8))),
                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe') // Renommé le label pour plus de clarté
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null) // Gère les cas où le champ est vide
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('identifiant_unique_somacif')
                    ->label('ID Unique')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pointsDeVente_count')
                    ->counts('pointsDeVente')
                    ->label('Points de Vente')
                    ->badge(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('telephone'),
                Tables\Columns\TextColumn::make('email'), // Ajout de la colonne email
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PointsDeVenteRelationManager::class,
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\ReglementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
            'view' => Pages\ViewClient::route('/{record}'),
        ];
    }
}