<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FournisseurResource\Pages;
use App\Models\Fournisseur;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FournisseurResource extends Resource
{
    protected static ?string $model = Fournisseur::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Partenaires';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations sur l\'entreprise')
                    ->schema([
                        Forms\Components\TextInput::make('nom_entreprise')
                            ->label('Nom de l\'entreprise')
                            ->required(),
                        Forms\Components\Textarea::make('adresse')
                            ->label('Adresse')
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Contact Principal')
                    ->schema([
                        Forms\Components\TextInput::make('nom_contact')
                            ->label('Nom du contact'),
                        Forms\Components\TextInput::make('telephone_contact')
                            ->label('Téléphone')
                            ->tel(),
                        Forms\Components\TextInput::make('email_contact')
                            ->label('Email')
                            ->email()
                            ->unique(ignoreRecord: true) // Ajout de la validation unique
                            ->nullable(), // Optionnel
                    ])->columns(3),
                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes internes'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom_entreprise')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nom_contact')
                    ->label('Contact')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telephone_contact')
                    ->label('Téléphone'),
                Tables\Columns\TextColumn::make('email_contact') // Ajout de la colonne email
                    ->label('Email'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListFournisseurs::route('/'),
            'create' => Pages\CreateFournisseur::route('/create'),
            'edit' => Pages\EditFournisseur::route('/{record}/edit'),
        ];
    }
}