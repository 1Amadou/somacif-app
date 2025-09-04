<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReglementResource\Pages;
use App\Models\Client;
use App\Models\Order;
use App\Models\Reglement;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReglementResource extends Resource
{
    protected static ?string $model = Reglement::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Ventes & Commandes';
    protected static ?int $navigationSort = 2;
    protected static ?string $label = 'Règlement Client';
    protected static ?string $pluralLabel = 'Règlements Clients';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations du Règlement')
                    ->schema([
                        Select::make('client_id')
                            ->relationship('client', 'nom', fn (Builder $query) => $query->whereNotNull('nom'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('orders', []))
                            ->label('Client'),
                        DatePicker::make('date_reglement')
                            ->required()
                            ->default(now())
                            ->label('Date du règlement'),
                        TextInput::make('montant_verse')
                            ->numeric()
                            ->required()
                            ->prefix('FCFA')
                            ->label('Montant Versé'),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Section::make('Commandes Associées')
                    ->description('Sélectionnez les commandes que ce règlement concerne.')
                    ->collapsible()
                    ->schema([
                        Select::make('orders')
                            ->label('Commandes à Régler')
                            ->multiple()
                            ->relationship('orders', 'numero_commande')
                            ->options(function (Get $get): Collection {
                                $clientId = $get('client_id');
                                if (!$clientId) {
                                    return collect();
                                }
                                return Order::query()
                                    ->where('client_id', $clientId)
                                    ->whereIn('statut', ['livree', 'en_cours_livraison'])
                                    ->whereIn('statut_paiement', ['non_payee', 'Partiellement réglé'])
                                    ->get()
                                    ->mapWithKeys(function ($order) {
                                        return [$order->id => "{$order->numero_commande} - Reste à payer: " . number_format($order->montant_total - $order->montant_paye, 0, ',', ' ') . " FCFA"];
                                    });
                            })
                            ->preload()
                            ->live(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.nom')->searchable()->sortable(),
                TextColumn::make('date_reglement')->date('d/m/Y')->sortable(),
                TextColumn::make('montant_verse')->money('XOF')->sortable(),
                TextColumn::make('orders.numero_commande')
                    ->badge()
                    ->label('Commandes Réglées'),
                TextColumn::make('methode_paiement')->searchable()->sortable(),
                TextColumn::make('user.name')->label('Enregistré par')->sortable(),
            ])
            ->defaultSort('date_reglement', 'desc')
            ->filters([])
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
        return [];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReglements::route('/'),
            'create' => Pages\CreateReglement::route('/create'),
            'edit' => Pages\EditReglement::route('/{record}/edit'),
        ];
    }
}