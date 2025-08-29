<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use App\Models\PointDeVente;
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
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Informations de Connexion (Portail Client)')
                    ->schema([
                        Forms\Components\TextInput::make('identifiant_unique_somacif')
                            ->label('Identifiant Unique')
                            ->helperText('Généré automatiquement à la création. Non modifiable.')
                            ->disabled()
                            ->dehydrated(fn ($state) => filled($state)) // S'assure qu'il est sauvegardé à la création
                            ->default(fn () => 'SOM-' . Str::upper(Str::random(8))),
                        Forms\Components\TextInput::make('password')
                            ->label('Nouveau Mot de Passe')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->helperText('Laissez vide pour ne pas changer le mot de passe.'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Points de Vente Associés')
                    ->schema([
                         Forms\Components\Select::make('pointsDeVente')
                            ->relationship('pointsDeVente', 'nom')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Associez ce client à un ou plusieurs points de vente.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('identifiant_unique_somacif')->label('ID Unique')->searchable(),
                Tables\Columns\TextColumn::make('pointsDeVente.nom')->label('Points de Vente')->badge(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('telephone'),
            ])
            ->filters([
                //
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
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\ReglementsRelationManager::class, // Affiche les règlements de ce client
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