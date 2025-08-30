<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LivreurResource\Pages;
use App\Models\Livreur;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class LivreurResource extends Resource
{
    protected static ?string $model = Livreur::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Gestion des Utilisateurs';

    // AMÉLIORATION : On utilise notre attribut virtuel pour les titres
    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations Personnelles')
                    ->schema([
                        TextInput::make('prenom')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('nom')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
                
                Section::make('Coordonnées et Connexion')
                    ->schema([
                        TextInput::make('telephone')
                            ->tel()
                            ->required()
                            ->maxLength(255)
                            ->unique(Livreur::class, 'telephone', ignoreRecord: true),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->unique(Livreur::class, 'email', ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->label('Mot de passe'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // AMÉLIORATION : On affiche le nom complet directement
                TextColumn::make('full_name')
                    ->label('Nom Complet')
                    ->searchable(['nom', 'prenom'])
                    ->sortable(['prenom', 'nom']), 
                TextColumn::make('telephone')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLivreurs::route('/'),
            'create' => Pages\CreateLivreur::route('/create'),
            'edit' => Pages\EditLivreur::route('/{record}/edit'),
        ];
    }    
}