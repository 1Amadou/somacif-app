<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReglementResource\Pages;
use App\Models\Client;
use App\Models\Order;
use App\Models\Reglement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\Filament\Resources\OrderResource;
use Filament\Tables\Filters\TernaryFilter;

class ReglementResource extends Resource
{
    protected static ?string $model = Reglement::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Ventes & Commandes';
    protected static ?string $label = 'Règlement Client';
    protected static ?string $pluralLabel = 'Règlements Clients';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Générales')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'nom')
                            ->searchable()->preload()->required()->live()
                            ->afterStateUpdated(fn (Set $set) => $set('order_id', null)),

                        Forms\Components\Select::make('order_id')
                            ->label('Commande Concernée')
                            ->options(function (Get $get, ?Reglement $record): Collection {
                                $clientId = $get('client_id') ?? $record?->client_id;
                                if (!$clientId) return collect();
                                return Order::query()
                                    ->where('client_id', $clientId)
                                    ->where('statut', '!=', 'annulee')
                                    ->with('pointDeVente')
                                    ->get()
                                    ->mapWithKeys(fn ($order) => [$order->id => $order->numero_commande . ' (' . $order->pointDeVente?->nom . ')']);
                            })
                            ->searchable()->live()->required()
                            ->afterStateUpdated(function (Set $set) {
                                $set('details', []);
                                $set('montant_calcule', 0);
                            }),
                        
                        Forms\Components\DatePicker::make('date_reglement')->required()->default(now()),
                            
                        Forms\Components\TextInput::make('montant_verse')
                            ->numeric()->required()->prefix('FCFA')
                            ->label('Montant Versé par le Client')
                            ->live(onBlur: true)
                            ->rules([
                                fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $montantCalcule = $get('montant_calcule');
                                    if ($value != $montantCalcule) {
                                        $fail("Le montant versé doit être identique au montant total des ventes déclarées (" . number_format($montantCalcule) . " FCFA).");
                                    }
                                },
                            ]),

                        Forms\Components\Select::make('methode_paiement')
                            ->options(['especes' => 'Espèces', 'cheque' => 'Chèque', 'virement' => 'Virement', 'mobile_money' => 'Mobile Money'])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Détail des Ventes (pour le déstockage)')
                    ->description("Déclarez ici chaque carton vendu et le prix de vente réel.")
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('unite_de_vente_id')
                                    ->label('Article Vendu')
                                    ->options(function (Get $get): array {
                                        $orderId = $get('../../order_id');
                                        if (!$orderId) return [];
                                        
                                        $order = Order::find($orderId);
                                        return $order?->items
                                            ->pluck('uniteDeVente.nom_complet', 'unite_de_vente_id')
                                            ->toArray() ?? [];
                                    })
                                    ->required()->searchable(),
                                
                                Forms\Components\TextInput::make('quantite_vendue')
                                    ->numeric()->required()->label('Qté Vendue')
                                    ->live(onBlur: true)
                                    ->suffix(function (Get $get): string {
                                        $orderId = $get('../../order_id');
                                        $uniteDeVenteId = $get('unite_de_vente_id');
                                        if (!$orderId || !$uniteDeVenteId) return '';

                                        $order = Order::find($orderId);
                                        $stockActuel = $order->pointDeVente?->lieuDeStockage?->inventories()
                                            ->where('unite_de_vente_id', $uniteDeVenteId)->value('quantite_stock') ?? 0;

                                        return "(Stock PDV: {$stockActuel})";
                                    })
                                    ->rules([
                                        fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $orderId = $get('../../order_id');
                                            $uniteDeVenteId = $get('unite_de_vente_id');
                                            if (!$orderId || !$uniteDeVenteId || is_null($value)) return;

                                            $lieuStockage = Order::find($orderId)->pointDeVente?->lieuDeStockage;
                                            $stockActuel = $lieuStockage?->inventories()->where('unite_de_vente_id', $uniteDeVenteId)->value('quantite_stock') ?? 0;
                                            
                                            if ($value > $stockActuel) {
                                                $fail("La quantité vendue ({$value}) dépasse le stock actuel du point de vente ({$stockActuel}).");
                                            }
                                        },
                                    ]),
                                
                                Forms\Components\TextInput::make('prix_de_vente_unitaire')
                                    ->numeric()->required()->label('Prix de Vente Unitaire Réel')->live(onBlur: true),
                            ])
                            ->columns(3)
                            ->addActionLabel('Ajouter une ligne de vente')
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateMontantCalcule($get, $set))
                            ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action->after(fn (Get $get, Set $set) => self::updateMontantCalcule($get, $set))),
                            
                        Forms\Components\TextInput::make('montant_calcule')
                            ->numeric()->readOnly()->prefix('FCFA')
                            ->label('Montant Total des Ventes Déclarées'),
                    ])->hidden(fn (Get $get) => !$get('order_id')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('date_reglement')->date('d/m/Y')->label('Date')->sortable(),
                Tables\Columns\TextColumn::make('montant_verse')->money('XOF')->sortable(),
                Tables\Columns\TextColumn::make('order.numero_commande')
                    ->label('Commande')
                    ->badge()
                    ->searchable()
                    ->url(fn (Reglement $record): string => OrderResource::getUrl('view', ['record' => $record->order_id]))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('methode_paiement')->badge(),
                Tables\Columns\TextColumn::make('user.name')->label('Enregistré par')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date_reglement', 'desc')
            ->filters([
                SelectFilter::make('client_id')
                    ->label('Client')
                    ->options(Client::pluck('nom', 'id')->all())
                    ->searchable(),
                Filter::make('date_reglement')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Du'),
                        Forms\Components\DatePicker::make('created_until')->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_reglement', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_reglement', '<=', $date),
                            );
                    }),
                SelectFilter::make('methode_paiement')
                    ->options([
                        'especes' => 'Espèces',
                        'cheque' => 'Chèque',
                        'virement' => 'Virement',
                        'mobile_money' => 'Mobile Money',
                    ]),
                    TernaryFilter::make('is_vente_directe')
                    ->label('Type de Vente d\'Origine')
                    ->placeholder('Toutes')
                    ->trueLabel('Vente Directe')
                    ->falseLabel('Bon de Commande')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('order', fn ($q) => $q->where('is_vente_directe', true)),
                        false: fn (Builder $query) => $query->whereHas('order', fn ($q) => $q->where('is_vente_directe', false)),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informations Générales')
                    ->schema([
                        Infolists\Components\TextEntry::make('client.nom'),
                        Infolists\Components\TextEntry::make('order.numero_commande')
                            ->label('Commande Concernée')
                            ->url(fn (Reglement $record): string => OrderResource::getUrl('view', ['record' => $record->order_id]))
                            ->openUrlInNewTab(),
                        Infolists\Components\TextEntry::make('date_reglement')->date('d/m/Y'),
                        Infolists\Components\TextEntry::make('montant_verse')->money('XOF')->color('success'),
                        Infolists\Components\TextEntry::make('methode_paiement')->badge(),
                        Infolists\Components\TextEntry::make('user.name')->label('Enregistré par'),
                    ])->columns(3),
                
                Section::make('Détail des Ventes Enregistrées pour ce Règlement')
                    ->schema([
                        RepeatableEntry::make('details')
                            ->schema([
                                Infolists\Components\TextEntry::make('uniteDeVente.nom_complet')->label('Article')->columnSpan(2),
                                Infolists\Components\TextEntry::make('quantite_vendue')->label('Qté Vendue'),
                                Infolists\Components\TextEntry::make('prix_de_vente_unitaire')->label('Prix de Vente')->money('XOF'),
                            ])->columns(4)->label(''),
                    ]),
            ]);
    }
    
    public static function updateMontantCalcule(Get $get, Set $set): void
    {
        $details = $get('details');
        $total = 0;
        if (is_array($details)) {
            foreach ($details as $item) {
                $total += ($item['quantite_vendue'] ?? 0) * ($item['prix_de_vente_unitaire'] ?? 0);
            }
        }
        $set('montant_calcule', $total);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReglements::route('/'),
            'create' => Pages\CreateReglement::route('/create'),
            'view' => Pages\ViewReglement::route('/{record}'),
            'edit' => Pages\EditReglement::route('/{record}/edit'),
        ];
    }
}