<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\UniteDeVente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Ventes';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        // ... (Le formulaire est déjà correct et ne change pas)
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Détails de la commande')
                        ->schema([
                            Forms\Components\TextInput::make('numero_commande')->default('SOMACIF-' . random_int(100000, 999999))->disabled()->dehydrated()->required(),
                            Forms\Components\Select::make('client_id')->relationship('client', 'nom')->searchable()->required(),
                            Forms\Components\Select::make('statut')->options(['Reçue' => 'Reçue', 'Validée' => 'Validée', 'En préparation' => 'En préparation', 'Expédiée' => 'Expédiée', 'Livrée' => 'Livrée', 'Annulée' => 'Annulée'])->required()->default('Reçue'),
                            Forms\Components\Textarea::make('notes')->columnSpanFull(),
                        ])->columns(2),
                    Forms\Components\Wizard\Step::make('Articles de la commande')
                        ->schema([
                            Forms\Components\Repeater::make('items')->relationship()->schema([
                                Forms\Components\Select::make('unite_de_vente_id')->label('Produit (Unité / Calibre)')->options(UniteDeVente::where('stock', '>', 0)->pluck('nom_unite', 'id'))->searchable()->required()->reactive()->afterStateUpdated(fn ($state, Set $set) => $set('prix_unitaire', UniteDeVente::find($state)?->prix_unitaire)),
                                Forms\Components\TextInput::make('quantite')->label('Nombre de cartons')->numeric()->required()->default(1)->live(onBlur: true),
                                Forms\Components\TextInput::make('prix_unitaire')->label('Prix Unitaire (par carton)')->numeric()->required()->live(onBlur: true),
                            ])->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set))->addActionLabel('Ajouter un article')->columns(3),
                        ]),
                    Forms\Components\Wizard\Step::make('Totaux et Validation')->schema([
                        Forms\Components\TextInput::make('montant_total')->label('Montant Total')->numeric()->readOnly()->prefix('CFA'),
                    ])
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_commande')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('client.nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('statut')->badge(),
                Tables\Columns\TextColumn::make('montant_total')->money('cfa')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y')->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    // ON REMPLACE L'ANCIEN RELATION MANAGER DE PAIEMENT PAR CELUI DES RÈGLEMENTS
    public static function getRelations(): array
    {
        return [
            RelationManagers\ReglementsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $total = 0;
        $items = $get('items');
        if (is_array($items)) {
            foreach ($items as $item) {
                if (!empty($item['quantite']) && !empty($item['prix_unitaire'])) {
                    $total += $item['quantite'] * $item['prix_unitaire'];
                }
            }
        }
        $set('montant_total', $total);
    }
}