<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VenteDirecteResource\Pages;
use App\Models\Client;
use App\Models\Order;
use App\Models\PointDeVente;
use App\Models\UniteDeVente;
use App\Models\VenteDirecte;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class VenteDirecteResource extends Resource
{
    // Le modèle de base reste VenteDirecte pour la structure, mais la table affichera des Commandes.
    protected static ?string $model = VenteDirecte::class; 
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Ventes & Commandes';
    protected static ?int $navigationSort = 2;
    protected static ?string $label = 'Vente Directe';
    protected static ?string $pluralLabel = 'Ventes Directes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations sur la Vente')
                    ->schema([
                        Forms\Components\TextInput::make('numero_facture')->default('FD-' . strtoupper(uniqid()))->disabled()->dehydrated()->required(),
                        Forms\Components\Select::make('client_id')->relationship('client', 'nom')->searchable()->preload()->required()->live()->label('Client'),
                        Forms\Components\Select::make('point_de_vente_id')->label('Point de Vente de Destination')
                            ->options(fn (Get $get): Collection => PointDeVente::query()->where('responsable_id', $get('client_id'))->pluck('nom', 'id'))
                            ->searchable()->preload()->required()->visible(fn (Get $get) => $get('client_id')),
                        Forms\Components\DatePicker::make('date_vente')->default(now())->required()->label('Date de la vente'),
                    ])->columns(2),
                Forms\Components\Section::make('Articles Vendus (depuis l\'Entrepôt Principal)')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->schema([
                                Forms\Components\Select::make('unite_de_vente_id')->label('Produit (Unité de vente)')
                                    ->options(fn () => UniteDeVente::query()->get()->filter(fn ($unite) => $unite->stock_entrepôt_principal > 0)->pluck('nom_complet_with_stock', 'id'))
                                    ->searchable()->preload()->required()->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $unite = UniteDeVente::find($state);
                                        $set('prix_unitaire', $unite?->prix_particulier ?? 0);
                                    }),
                                Forms\Components\TextInput::make('quantite')->numeric()->required()->live(onBlur: true)->default(1),
                                Forms\Components\TextInput::make('prix_unitaire')->numeric()->required()->live(onBlur: true),
                            ])->columns(3)->addActionLabel('Ajouter un article')->live()
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::updateGrandTotal($get, $set))
                                ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action->after(fn (Get $get, Set $set) => self::updateGrandTotal($get, $set))),
                    ]),
                Forms\Components\Section::make('Paiement et Résumé')
                    ->schema([
                        Forms\Components\TextInput::make('montant_total')->numeric()->readOnly()->prefix('FCFA')->label('Montant Total à Payer'),
                        Forms\Components\Select::make('methode_paiement')->options(['especes' => 'Espèces', 'cheque' => 'Chèque', 'virement' => 'Virement', 'mobile_money' => 'Mobile Money'])->required(),
                        Forms\Components\Textarea::make('notes')->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    /**
     * CORRECTION : La table affiche maintenant les Commandes qui sont des Ventes Directes.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->where('is_vente_directe', true))
            ->columns([
                Tables\Columns\TextColumn::make('numero_commande')->label('N° Facture/Commande')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('client.nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->date('d/m/Y')->label('Date')->sortable(),
                Tables\Columns\TextColumn::make('montant_total')->money('XOF')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // L'action pointe vers la vue de la Commande
                Tables\Actions\ViewAction::make()->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
            ]);
    }
    
    public static function updateGrandTotal(Get $get, Set $set): void
    {
        $total = collect($get('items'))->sum(fn (array $item) => ($item['quantite'] ?? 0) * ($item['prix_unitaire'] ?? 0));
        $set('montant_total', $total);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVenteDirectes::route('/'),
            'create' => Pages\CreateVenteDirecte::route('/create'),
        ];
    }
}