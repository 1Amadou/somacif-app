<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Client;
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
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Commandes de Distribution';
    protected static ?string $navigationGroup = 'Ventes & Distribution';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Client & Destination')
                        ->description('Sélectionnez le client et son point de vente de livraison.')
                        ->schema([
                            Forms\Components\Select::make('client_id')
                                ->relationship('client', 'nom')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn (Set $set) => $set('point_de_vente_id', null)),

                            Forms\Components\Select::make('point_de_vente_id')
                                ->label('Point de Vente de Destination')
                                ->options(fn (Get $get): Collection => PointDeVente::query()
                                    ->where('responsable_id', $get('client_id'))
                                    ->pluck('nom', 'id'))
                                ->searchable()
                                ->required()
                                ->visible(fn (Get $get): bool => filled($get('client_id')) && Client::find($get('client_id'))?->pointsDeVente()->exists()),

                            Forms\Components\Placeholder::make('no_pdv_placeholder')
                                ->content(function (Get $get) {
                                    $clientId = $get('client_id');
                                    if (filled($clientId) && !Client::find($clientId)?->pointsDeVente()->exists()) {
                                        $clientUrl = ClientResource::getUrl('edit', ['record' => $clientId]);
                                        return new HtmlString(
                                            '<div class="fi-placeholder text-sm text-gray-500" style="padding: 10px; border-radius: 5px; background-color: #fffbe6; color: #f59e0b;">' .
                                            '<strong>Attention:</strong> Ce client n\'a aucun point de vente associé. <br>' .
                                            '<a href="' . $clientUrl . '" style="text-decoration: underline; font-weight: bold;">Cliquez ici pour lui en assigner un avant de continuer.</a>' .
                                            '</div>'
                                        );
                                    }
                                    return '';
                                })
                                ->visible(fn (Get $get): bool => filled($get('client_id')) && !Client::find($get('client_id'))?->pointsDeVente()->exists()),
                        ]),

                    Forms\Components\Wizard\Step::make('Contenu de la Commande')
                        ->description('Ajoutez les produits et les quantités à livrer.')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->minItems(1)
                                ->schema([
                                    Forms\Components\Select::make('unite_de_vente_id')
                                        ->label('Produit (Unité / Calibre)')
                                        ->options(UniteDeVente::with('product')->get()->mapWithKeys(function ($unite) {
                                            return [$unite->id => "{$unite->nom_complet} (Stock: {$unite->stock})"];
                                        }))
                                        ->searchable()
                                        ->getSearchResultsUsing(fn (string $search) => UniteDeVente::query()
                                            ->join('products', 'unite_de_ventes.product_id', '=', 'products.id')
                                            ->where('unite_de_ventes.stock', '>', 0)
                                            ->where(function (Builder $query) use ($search) {
                                                $query->where('products.nom', 'like', "%{$search}%")
                                                    ->orWhere('unite_de_ventes.nom_unite', 'like', "%{$search}%")
                                                    ->orWhere('unite_de_ventes.calibre', 'like', "%{$search}%");
                                            })
                                            ->pluck('unite_de_ventes.nom_complet', 'unite_de_ventes.id'))
                                        ->getOptionLabelFromRecordUsing(fn (UniteDeVente $record) => "{$record->nom_complet} (Stock: {$record->stock})")
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                            $unite = UniteDeVente::find($state);
                                            $client = Client::find($get('../../client_id'));
                                            if ($unite && $client) {
                                                $price = match ($client->type) {
                                                    'Grossiste' => $unite->prix_grossiste,
                                                    'Hôtel/Restaurant' => $unite->prix_hotel_restaurant,
                                                    'Particulier' => $unite->prix_particulier,
                                                    default => $unite->prix_unitaire,
                                                };
                                                $set('prix_unitaire', $price);
                                            }
                                        })
                                        ->distinct()
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->columnSpan(4),

                                    Forms\Components\TextInput::make('quantite')
                                        ->label('Quantité')
                                        ->numeric()
                                        ->required()
                                        ->default(1)
                                        ->minValue(1)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                            $prix = $get('prix_unitaire');
                                            if ($state && $prix) {
                                                $set('montant_ligne', $state * $prix);
                                            }
                                        })
                                        ->columnSpan(2),
                                    
                                    Forms\Components\TextInput::make('prix_unitaire')
                                        ->label('Prix Unitaire')
                                        ->numeric()
                                        ->required()
                                        ->helperText('Auto-défini. Le modifier mettra à jour le prix interne.')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                            $quantite = $get('quantite');
                                            if ($state && $quantite) {
                                                $set('montant_ligne', $state * $quantite);
                                            }
                                        })
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('montant_ligne')
                                        ->label('Montant Ligne')
                                        ->numeric()
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->columnSpan(2)
                                        ->default(0),
                                ])
                                ->columns(8)
                                ->addActionLabel('Ajouter un produit')
                                ->reorderable(true),
                        ]),
                    
                    Forms\Components\Wizard\Step::make('Statut & Finalisation')
                        ->description('Vérifiez les informations et validez la commande.')
                        ->schema([
                            Forms\Components\TextInput::make('numero_commande')
                                ->label('Numéro de Commande')
                                ->default('CMD-' . Str::upper(Str::random(8)))
                                ->disabled()
                                ->required()
                                ->unique(Order::class, 'numero_commande', ignoreRecord: true)
                                ->dehydrated(),
                            
                            Forms\Components\Select::make('statut')
                                ->options([
                                    'en_attente' => 'En attente',
                                    'validee' => 'Validée (Déclenche le transfert de stock)',
                                    'prete_pour_livraison' => 'Prête pour livraison',
                                    'en_cours_de_livraison' => 'En cours de livraison',
                                    'livree' => 'Livrée',
                                    'annulee' => 'Annulée',
                                ])
                                ->required()
                                ->default('en_attente'),
                            
                            Forms\Components\Textarea::make('notes')
                                ->columnSpanFull(),
                        ])
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_commande')
                    ->label('N° Commande')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('client.nom')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('pointDeVente.nom')
                    ->label('Point de Vente')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en_attente' => 'warning', 
                        'validee' => 'success', 
                        'prete_pour_livraison' => 'info',
                        'en_cours_de_livraison' => 'primary', 
                        'livree' => 'success', 
                        'annulee' => 'danger',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('montant_total')
                    ->label('Montant Total')
                    ->numeric()
                    ->sortable()
                    ->money('XOF'),
                
                Tables\Columns\TextColumn::make('statut_paiement')
                    ->label('Paiement')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'non_paye' => 'danger', 
                        'partiellement_paye' => 'warning', 
                        'paye' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ]);
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