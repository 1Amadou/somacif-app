<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockTransfertResource\Pages;
use App\Models\Client;
use App\Models\Order;
use App\Models\PointDeVente;
use App\Models\StockTransfert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class StockTransfertResource extends Resource
{
    protected static ?string $model = StockTransfert::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Gestion de Stock';
    protected static ?string $navigationLabel = 'Réallocation de Commande';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Origine de la Marchandise')
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->label('Commande à modifier (Source)')
                            ->options(Order::where('statut', 'validee')->pluck('numero_commande', 'id'))
                            ->searchable()->preload()->live()->required()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('details', [])),
                        
                        Forms\Components\Placeholder::make('source_details')
                            ->label('Client et Point de Vente Source')
                            ->content(function (Get $get): string {
                                $orderId = $get('order_id');
                                if (!$orderId) return 'N/A';
                                $order = Order::with('client', 'pointDeVente')->find($orderId);
                                if (!$order) return 'N/A';
                                return $order->client->nom . ' / ' . $order->pointDeVente->nom;
                            }),
                    ])->columns(2),

                Forms\Components\Section::make('Nouvelle Destination')
                    ->schema([
                        Forms\Components\Select::make('destination_client_id')
                            ->label('Client de Destination')
                            ->options(Client::query()->pluck('nom', 'id'))
                            ->searchable()->preload()->live()->required()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('destination_point_de_vente_id', null)),
                        
                        Forms\Components\Select::make('destination_point_de_vente_id')
                            ->label('Point de Vente de Destination')
                            ->options(function (Get $get): Collection {
                                $clientId = $get('destination_client_id');
                                if (!$clientId) return collect();
                                return PointDeVente::query()->where('responsable_id', $clientId)->pluck('nom', 'id');
                            })
                            ->searchable()->preload()->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Articles à Réallouer')
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->schema([
                                Forms\Components\Select::make('unite_de_vente_id')
                                    ->label('Article')
                                    ->options(function (Get $get) {
                                        $orderId = $get('../../order_id');
                                        if (!$orderId) return [];
                                        $order = Order::with('items.uniteDeVente', 'pointDeVente.lieuDeStockage.inventories')->find($orderId);
                                        if (!$order?->pointDeVente?->lieuDeStockage) return [];
                                        
                                        return $order->items->mapWithKeys(function ($item) use ($order) {
                                            $stockDisponible = $order->pointDeVente->lieuDeStockage->inventories
                                                ->where('unite_de_vente_id', $item->unite_de_vente_id)
                                                ->first()?->quantite_stock ?? 0;

                                            if ($stockDisponible > 0) {
                                                return [$item->unite_de_vente_id => $item->uniteDeVente->nom_complet . ' (Stock Dispo: ' . $stockDisponible . ')'];
                                            }
                                            return [];
                                        });
                                    })
                                    ->required()->searchable()->live()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                
                                Forms\Components\TextInput::make('quantite')
                                    ->numeric()->required()->minValue(1)
                                    ->rules([
                                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $orderId = $get('../../order_id');
                                            $uniteDeVenteId = $get('unite_de_vente_id');
                                            $order = Order::find($orderId);
                                            $stockDisponible = $order?->pointDeVente?->lieuDeStockage?->inventories()
                                                ->where('unite_de_vente_id', $uniteDeVenteId)->value('quantite_stock') ?? 0;

                                            if ($value > $stockDisponible) {
                                                $fail("La quantité ({$value}) dépasse le stock disponible ({$stockDisponible}).");
                                            }
                                        },
                                    ]),
                            ])->columns(2),
                    ])->hidden(fn (Get $get) => !$get('order_id')),

                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
                
                // CORRECTION : Affiche le numéro de commande et ne génère l'URL que si l'ID existe.
                Tables\Columns\TextColumn::make('order.numero_commande')->label('Commande Source')->badge()
                    ->url(fn (StockTransfert $record): ?string => $record->order_id ? OrderResource::getUrl('view', ['record' => $record->order_id]) : null)
                    ->openUrlInNewTab()
                    ->placeholder('N/A'),

                // CORRECTION : Idem pour la commande de destination.
                Tables\Columns\TextColumn::make('newOrder.numero_commande')->label('Commande Destination')->badge()
                    ->url(fn (StockTransfert $record): ?string => $record->new_order_id ? OrderResource::getUrl('view', ['record' => $record->new_order_id]) : null)
                    ->openUrlInNewTab()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('user.name')->label('Effectué par')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockTransferts::route('/'),
            'create' => Pages\CreateStockTransfert::route('/create'),
        ];
    }
}
