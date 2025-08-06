<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers; // Important
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Partenaires';
    protected static ?int $navigationSort = 2;

    public static function getRecordTitle(?Model $record): ?string
    {
        return $record?->nom ?? 'Partenaire';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Principales')->schema([
                    Forms\Components\TextInput::make('nom')->required(),
                    Forms\Components\Select::make('type')->options(['Grossiste' => 'Grossiste', 'Hôtel/Restaurant' => 'Hôtel/Restaurant', 'Particulier' => 'Particulier'])->required(),
                    Forms\Components\Select::make('status')->options(['pending' => 'En attente', 'approved' => 'Approuvé', 'rejected' => 'Rejeté'])->required(),
                    Forms\Components\TextInput::make('identifiant_unique_somacif')->label('Identifiant Unique'),
                ])->columns(2),
                Forms\Components\Section::make('Coordonnées & Logistique')->schema([
                    Forms\Components\TextInput::make('telephone')->tel()->required(),
                    Forms\Components\TextInput::make('email')->email(),
                    Forms\Components\TagsInput::make('entrepots_de_livraison')->label('Entrepôts de livraison'),
                ])->columns(2),
                Forms\Components\Section::make('Documents & Légal')->schema([
                    Forms\Components\FileUpload::make('contract_path')
                        ->label('Contrat signé (PDF)')
                        ->disk('public')->directory('contracts')
                        ->acceptedFileTypes(['application/pdf']),
                    Forms\Components\Placeholder::make('terms_accepted_at')
                        ->label('CGU acceptées le')
                        ->content(fn (?Client $record) => $record?->terms_accepted_at ? $record->terms_accepted_at->format('d/m/Y à H:i') : 'Non acceptées'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger',
                })->formatStateUsing(fn (string $state): string => match ($state) {
                    'pending' => 'En attente', 'approved' => 'Approuvé', 'rejected' => 'Rejeté',
                }),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('telephone'),
                Tables\Columns\IconColumn::make('contract_path')->label('Contrat')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
    
    // ON DÉCLARE LES GESTIONNAIRES QUI APPARAÎTRONT SUR LA PAGE DE VUE
    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\LoginLogsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            // La page de vue va maintenant afficher les "relations" ci-dessus
            'view' => Pages\ViewClient::route('/{record}'), 
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }    
}