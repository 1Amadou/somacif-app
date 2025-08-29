<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VenteDirecteResource\Pages;
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

class VenteDirecteResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Ventes';
    protected static ?string $navigationLabel = 'Vente Directe';
    protected static ?string $modelLabel = 'Vente Directe';

    // On s'assure de ne voir que les ventes directes dans la liste
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_vente_directe', true);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails de la Vente')
                    ->description('Enregistrez ici une vente rapide au comptoir.')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'nom')
                            ->label('Client')
                            ->searchable()
                            ->required()
                            ->helperText('Sélectionnez le client qui achète le produit. Il peut être un client de passage ou un partenaire.'),
                        Forms\Components\Select::make('point_de_vente_id')
                            ->label('Stock Utilisé (Point de Vente)')
                            ->options(PointDeVente::all()->pluck('nom', 'id'))
                            ->live() // Indispensable pour filtrer les produits
                            ->required()
                            ->helperText('Sélectionnez le point de vente d\'où le stock est physiquement retiré.'),
                    ])->columns(2),

                Forms\Components\Section::make('Articles Vendus')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('unite_de_vente_id')
                                    ->label('Produit Vendu')
                                    ->options(function (Get $get): Collection {
                                        // La liste des produits dépend maintenant du POINT DE VENTE sélectionné, et non du client.
                                        $pointDeVenteId = $get('../../point_de_vente_id');
                                        if (!$pointDeVenteId) { return collect(); }

                                        $uniteDeVenteIds = \App\Models\Inventory::where('point_de_vente_id', $pointDeVenteId)
                                            ->where('quantite_stock', '>', 0)
                                            ->pluck('unite_de_vente_id');
                                        
                                        return UniteDeVente::whereIn('id', $uniteDeVenteIds)
                                            ->get()
                                            ->mapWithKeys(fn ($unite) => [$unite->id => $unite->nom_complet]);
                                    })
                                    ->searchable()->required()->reactive()
                                    ->afterStateUpdated(fn ($state, Set $set) => $set('prix_unitaire', UniteDeVente::find($state)?->prix_unitaire)),
                                Forms\Components\TextInput::make('quantite')
                                    ->numeric()->required()->live(onBlur: true),
                                Forms\Components\TextInput::make('prix_unitaire')
                                    ->numeric()->required()->live(onBlur: true),
                            ])
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $total = 0;
                                foreach ($get('items') as $item) {
                                    if(!empty($item['quantite']) && !empty($item['prix_unitaire'])) {
                                        $total += $item['quantite'] * $item['prix_unitaire'];
                                    }
                                }
                                $set('montant_total', $total);
                                $set('montant_paye', $total); // Pour une vente directe, le montant payé est le total
                            })
                            ->addActionLabel('Ajouter un produit')->columns(3),
                    ]),
                
                Forms\Components\Section::make('Finalisation')
                    ->schema([
                        Forms\Components\TextInput::make('montant_total')->label('Montant Total')->readOnly()->prefix('CFA'),
                        // Le champ de notes que tu as demandé
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes (optionnel)')
                    ]),

                // Champs cachés pour automatiser l'enregistrement
                Forms\Components\Hidden::make('is_vente_directe')->default(true),
                Forms\Components\Hidden::make('statut')->default('Livrée'),
                Forms\Components\Hidden::make('statut_paiement')->default('Complètement réglé'),
                Forms\Components\Hidden::make('numero_commande')->default('VD-' . strtoupper(uniqid())),
                Forms\Components\Hidden::make('montant_paye')->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Date Vente')->dateTime('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('client.nom')->searchable(),
                Tables\Columns\TextColumn::make('pointDeVente.nom')->label('Stock utilisé'),
                Tables\Columns\TextColumn::make('montant_total')->money('cfa'),
            ])
            ->defaultSort('created_at', 'desc');
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVenteDirectes::route('/'),
            'create' => Pages\CreateVenteDirecte::route('/create'),
            'view' => Pages\ViewVenteDirecte::route('/{record}'),
        ];
    }
}