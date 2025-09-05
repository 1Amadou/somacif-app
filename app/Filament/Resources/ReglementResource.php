<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReglementResource\Pages;
use App\Models\Order;
use App\Models\Reglement;
use App\Models\UniteDeVente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class ReglementResource extends Resource
{
    protected static ?string $model = Reglement::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Ventes & Commandes';
    protected static ?string $label = 'Règlement Client';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Générales')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'nom')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('order_id', null)),

                        Forms\Components\Select::make('order_id')
                            ->label('Commande Concernée par le Règlement')
                            ->options(function (Get $get, ?Reglement $record): Collection {
                                $clientId = $get('client_id') ?? $record?->client_id;
                                if (!$clientId) return collect();
                                return Order::query()
                                    ->where('client_id', $clientId)
                                    ->where('statut', '!=', 'annulee')
                                    ->with('pointDeVente')
                                    ->get()
                                    ->mapWithKeys(fn ($order) => [$order->id => $order->numero_commande . ' (' . $order->pointDeVente->nom . ')']);
                            })
                            ->searchable()
                            ->live()
                            ->required()
                            ->afterStateUpdated(function (Set $set, $state) {
                                $order = Order::find($state);
                                if ($order) {
                                    $set('details', []);
                                    $set('montant_calcule', 0);
                                }
                            }),
                        
                        Forms\Components\DatePicker::make('date_reglement')
                            ->required()
                            ->default(now()),
                            
                        // **CORRECTION : AJOUT DE LA RÈGLE DE VALIDATION**
                        Forms\Components\TextInput::make('montant_verse')
                            ->numeric()
                            ->required()
                            ->prefix('FCFA')
                            ->label('Montant Versé par le Client')
                            ->live(onBlur: true) // Validation en direct
                            ->rules([
                                fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $montantCalcule = $get('montant_calcule');
                                    if ($value != $montantCalcule) {
                                        $fail("Le montant versé doit être identique au montant total des ventes déclarées ({$montantCalcule} FCFA).");
                                    }
                                },
                            ]),

                        Forms\Components\Select::make('methode_paiement')
                            ->options(['especes' => 'Espèces', 'cheque' => 'Chèque', 'virement' => 'Virement', 'autre' => 'Autre'])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Détail des Ventes (pour le déstockage)')
                    ->description("Ajoutez une ligne pour chaque lot de produit vendu, même si c'est le même produit à un prix différent.")
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->schema([
                                Forms\Components\Select::make('unite_de_vente_id')
                                    ->label('Article Vendu')
                                    ->options(function (Get $get, ?Reglement $record): array {
                                        $orderId = $get('../../order_id') ?? $record?->order_id;
                                        if (!$orderId) return [];
                                        
                                        $order = Order::with('items.uniteDeVente.product')->find($orderId);
                                        return $order->items
                                            ->filter(fn ($item) => $item->uniteDeVente !== null && $item->uniteDeVente->nom_complet !== null)
                                            ->pluck('uniteDeVente.nom_complet', 'unite_de_vente_id')
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable(),
                                
                                Forms\Components\TextInput::make('quantite_vendue')
                                    ->numeric()
                                    ->required()
                                    ->label('Qté Vendue')
                                    ->live(onBlur: true)
                                    ->suffix(function (Get $get, $record) {
                                        $orderId = $get('../../order_id');
                                        $uniteDeVenteId = $get('unite_de_vente_id');
                                        if (!$orderId || !$uniteDeVenteId) return '';
                                        $orderItem = Order::find($orderId)->items()->where('unite_de_vente_id', $uniteDeVenteId)->first();
                                        $quantiteCommandee = $orderItem ? $orderItem->quantite : 0;
                                        return " / " . $quantiteCommandee;
                                    })
                                    ->rules([
                                        fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $orderId = $get('../../order_id');
                                            $uniteDeVenteId = $get('unite_de_vente_id');

                                            if (!$orderId || !$uniteDeVenteId || is_null($value)) return;
                                            
                                            $orderItem = Order::find($orderId)->items()->where('unite_de_vente_id', $uniteDeVenteId)->first();
                                            
                                            if ($orderItem && $value > $orderItem->quantite) {
                                                $fail("La quantité saisie ({$value}) est supérieure à la quantité commandée pour cet article ({$orderItem->quantite}).");
                                            }
                                        },
                                    ]),
                                
                                Forms\Components\TextInput::make('prix_de_vente_unitaire')
                                    ->numeric()
                                    ->required()
                                    ->label('Prix de Vente Unitaire')
                                    ->live(onBlur: true),
                            ])
                            ->columns(3)
                            ->addActionLabel('Ajouter une ligne de vente')
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateMontantCalcule($get, $set))
                            ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action->after(fn (Get $get, Set $set) => self::updateMontantCalcule($get, $set))),
                            
                        Forms\Components\TextInput::make('montant_calcule')
                            ->numeric()
                            ->readOnly()
                            ->prefix('FCFA')
                            ->label('Montant Total des Ventes Déclarées'),
                    ])->hidden(fn (Get $get, ?Reglement $record) => !$get('order_id') && !$record?->order_id),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations Générales')->schema([
                    Infolists\Components\TextEntry::make('client.nom'),
                    Infolists\Components\TextEntry::make('order.numero_commande')->label('Commande Concernée'),
                    Infolists\Components\TextEntry::make('date_reglement')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('montant_verse')->money('XOF'),
                    Infolists\Components\TextEntry::make('methode_paiement')->badge(),
                    Infolists\Components\TextEntry::make('user.name')->label('Enregistré par'),
                ])->columns(3),
                Infolists\Components\Section::make('Détail des Ventes Enregistrées')->schema([
                    Infolists\Components\RepeatableEntry::make('details')
                        ->schema([
                            Infolists\Components\TextEntry::make('unite_de_vente_id')
                                ->label('Article')
                                ->formatStateUsing(function ($state) {
                                    $unite = UniteDeVente::find($state);
                                    return $unite ? $unite->nom_complet : 'Article Inconnu';
                                })
                                ->columnSpan(2),
                            Infolists\Components\TextEntry::make('quantite_vendue')->label('Qté Vendue'),
                            Infolists\Components\TextEntry::make('prix_de_vente_unitaire')->label('Prix de Vente')->money('XOF'),
                        ])->columns(4)->label(''),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('date_reglement')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('montant_verse')->money('XOF')->sortable(),
                Tables\Columns\TextColumn::make('montant_calcule')->money('XOF')->sortable(),
                Tables\Columns\TextColumn::make('order.numero_commande')->badge(),
                Tables\Columns\TextColumn::make('user.name')->sortable(),
            ])
            ->defaultSort('date_reglement', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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