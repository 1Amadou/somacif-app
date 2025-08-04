<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Clients';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du Client')->schema([
                    Forms\Components\TextInput::make('nom')->required(),
                    Forms\Components\Select::make('type')
                        ->options(['Grossiste' => 'Grossiste', 'Hôtel/Restaurant' => 'Hôtel/Restaurant', 'Particulier' => 'Particulier'])
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->options(['pending' => 'En attente', 'approved' => 'Approuvé', 'rejected' => 'Rejeté'])
                        ->required(),
                    Forms\Components\TextInput::make('identifiant_unique_somacif')->label('Identifiant Unique SOMACIF')->required()->unique(ignoreRecord: true),
                ])->columns(2),

                Forms\Components\Section::make('Coordonnées')->schema([
                    Forms\Components\TextInput::make('telephone')->tel()->required()->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('email')->email()->unique(ignoreRecord: true),
                    Forms\Components\TagsInput::make('entrepots_de_livraison')
                    ->label('Entrepôts de livraison')
                    ->helperText('Appuyez sur Entrée après chaque adresse.')
                    ->columnSpanFull(),
                            ])->columns(2),
                        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'approved' => 'Approuvé',
                        'rejected' => 'Rejeté',
                    }),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('identifiant_unique_somacif')->label('Identifiant Unique')->searchable(),
                Tables\Columns\TextColumn::make('telephone'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'En attente', 'approved' => 'Approuvé', 'rejected' => 'Rejeté']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}