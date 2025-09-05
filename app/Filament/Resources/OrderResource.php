<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\PointDeVente;
use App\Models\UniteDeVente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Ventes & Commandes';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails de la Commande')
                    ->schema([
                        Forms\Components\TextInput::make('numero_commande')
                            ->default('CMD-' . random_int(1000, 9999))
                            ->disabled()->dehydrated()->required(),
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'nom', fn (Builder $query) => $query->whereNotNull('nom'))
                            ->searchable()->preload()->live()
                            ->afterStateUpdated(fn (Set $set) => $set('point_de_vente_id', null))
                            ->required()->label('Client'),
                        Forms\Components\Select::make('point_de_vente_id')
                            ->label('Point de Vente de Destination')
                            ->options(fn (Get $get): Collection => PointDeVente::query()
                                ->where('responsable_id', $get('client_id'))
                                ->pluck('nom', 'id'))
                            ->searchable()->preload()->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Statut & Livraison')
                    ->schema([
                        Forms\Components\Select::make('statut')
                            ->options(collect(OrderStatusEnum::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()]))
                            ->required()->live()->default(OrderStatusEnum::EN_ATTENTE->value)
                            ->disabled(fn (?Order $record) => $record && in_array($record->statut, [OrderStatusEnum::LIVREE, OrderStatusEnum::ANNULEE])),
                        
                        Forms\Components\Select::make('livreur_id')
                            ->relationship('livreur', 'nom')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->prenom} {$record->nom}")
                            ->searchable(['nom', 'prenom'])->preload()->label('Livreur à assigner'),
                    ]),

                Forms\Components\Section::make('Articles de la Commande')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('unite_de_vente_id')
                                    ->label('Produit (Unité de vente)')
                                    ->options(function () {
                                        return UniteDeVente::with('product')->get()->mapWithKeys(function ($unite) {
                                            // Affiche le stock principal directement dans la liste
                                            return [$unite->id => $unite->nom_complet . ' (Stock: ' . $unite->stock_principal . ')'];
                                        });
                                    })
                                    ->searchable()->preload()->required()->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $unite = UniteDeVente::find($state);
                                        // Utilise le prix particulier par défaut
                                        $set('prix_unitaire', $unite?->prix_particulier ?? 0);
                                    }),
                                Forms\Components\TextInput::make('quantite')
                                    ->numeric()->required()->live(onBlur: true)->default(1),
                                Forms\Components\TextInput::make('prix_unitaire')
                                    ->numeric()->required()->live(onBlur: true),
                                Forms\Components\Placeholder::make('prix_total_ligne')
                                    ->label('Total Ligne')
                                    ->content(fn (Get $get): string => number_format(($get('quantite') ?? 0) * ($get('prix_unitaire') ?? 0), 0, ',', ' ') . ' FCFA'),
                            ])
                            ->columns(4)
                            ->addActionLabel('Ajouter un article')
                            ->collapsible()->reorderable(false)->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateGrandTotal($get, $set))
                            ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action->after(fn (Get $get, Set $set) => self::updateGrandTotal($get, $set))),
                    ]),

                Forms\Components\Section::make('Résumé')
                    ->schema([
                        Forms\Components\TextInput::make('montant_total')
                            ->numeric()->readOnly()->prefix('FCFA')->label('Montant Total'),
                        Forms\Components\Textarea::make('notes')->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_commande')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('client.nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (OrderStatusEnum $state): string => $state->getColor())
                    ->formatStateUsing(fn (OrderStatusEnum $state): string => $state->getLabel())
                    ->sortable(),
                Tables\Columns\TextColumn::make('montant_total')->money('XOF')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d/m/Y')->label('Date')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }
    
    public static function updateGrandTotal(Get $get, Set $set): void
    {
        $total = 0;
        $items = $get('items');
        if (is_array($items)) {
            foreach ($items as $item) {
                $total += ($item['quantite'] ?? 0) * ($item['prix_unitaire'] ?? 0);
            }
        }
        $set('montant_total', $total);
    }
    
    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}