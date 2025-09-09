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
    protected static ?string $navigationGroup = 'Clients & Partenaires'; // Changement de groupe pour la cohérence
    protected static ?int $navigationSort = 2; // Ajustement du tri

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations sur le Partenaire')
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->required()->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->options([
                                'Grossiste' => 'Grossiste',
                                'Hôtel/Restaurant' => 'Hôtel/Restaurant',
                                'Particulier' => 'Particulier',
                            ])->required(),
                        Forms\Components\TextInput::make('telephone')
                            ->tel()->required()->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('email')
                            ->email()->maxLength(255)->unique(ignoreRecord: true)->nullable(),
                        // --- AMÉLIORATION : Ajout du champ Statut ---
                        Forms\Components\Toggle::make('status')
                            ->label('Compte Actif')
                            ->helperText('Si désactivé, le client ne pourra pas se connecter.')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(true), // Les nouveaux clients sont actifs par défaut
                    ])->columns(2),

                Forms\Components\Section::make('Informations de Connexion (Portail Client)')
                    ->schema([
                        Forms\Components\TextInput::make('identifiant_unique_somacif')
                            ->label('Identifiant Unique')
                            ->disabled()->dehydrated(fn ($state) => filled($state))
                            ->default(fn () => 'SOM-' . Str::upper(Str::random(8))),
                        Forms\Components\TextInput::make('password')
                            ->label('Nouveau mot de passe')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->helperText("Laissez vide si vous ne voulez pas changer le mot de passe."),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('identifiant_unique_somacif')->label('ID Unique')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                // --- AMÉLIORATION : Colonne de statut visuelle et interactive ---
                Tables\Columns\ToggleColumn::make('status')
                    ->label('Actif')
                    ->onColor('success')
                    ->offColor('danger'),
                Tables\Columns\TextColumn::make('telephone'),
                Tables\Columns\TextColumn::make('email')->searchable(),
            ])
            ->filters([
                // --- AMÉLIORATION : Filtre par statut ---
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Statut du compte')
                    ->boolean()
                    ->trueLabel('Actifs')
                    ->falseLabel('Inactifs'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PointsDeVenteRelationManager::class,
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\ReglementsRelationManager::class,
            RelationManagers\LoginLogsRelationManager::class,
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