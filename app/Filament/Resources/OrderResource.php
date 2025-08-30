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
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
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
                        ->description('Sélectionnez le client et son point de vente de livraison. Le point de vente changera dynamiquement selon le client.')
                        ->schema([
                            Forms\Components\Select::make('client_id')
                                ->label('Client')
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
                                ->visible(fn (Get $get) =>
                                    filled($get('client_id')) && Client::find($get('client_id'))?->pointsDeVente()->exists()),

                                    Forms\Components\Placeholder::make('client_points_de_vente_info')
                                    ->content(function (Get $get) {
                                        $clientId = $get('client_id');
                                        if (filled($clientId) && !Client::find($clientId)?->pointsDeVente()->exists()) {
                                            $url = ClientResource::getUrl('edit', ['record' => $clientId]);
                                            return new HtmlString(
                                                '<p class="text-yellow-700 bg-yellow-100 p-3 rounded">' .
                                                'Attention : Ce client n\'a aucun point de vente assigné. ' .
                                                '<a href="' . $url . '" class="underline font-semibold" target="_blank">Ajouter un point de vente</a> avant de continuer.' .
                                                '</p>'
                                            );
                                        }
                                        return '';
                                    })
                                ->visible(fn (Get $get) => filled($get('client_id')) && !Client::find($get('client_id'))?->pointsDeVente()->exists()),
                        ]),

                    Forms\Components\Wizard\Step::make('Articles et Quantités')
                        ->description('Ajoutez les produits, calibres et quantités. Sélectionnez uniquement parmi ceux en stock.')
                        ->schema([
                            Forms\Components\Repeater::make('items')
                                ->minItems(1)
                                ->schema([
                                    Forms\Components\Select::make('unite_de_vente_id')
                                        ->label('Produit (Unité / Calibre)')
                                        ->options(fn () => UniteDeVente::where('stock', '>', 0)
                                            ->get()
                                            ->mapWithKeys(fn ($item) =>
                                                [$item->id => "{$item->nom_complet} (Stock: {$item->stock})"])
                                            ->toArray())
                                        ->searchable()
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
                                        ->columnSpan(4),

                                    Forms\Components\TextInput::make('quantite')
                                        ->label('Quantité')
                                        ->numeric()
                                        ->required()
                                        ->default(1)
                                        ->minValue(1)
                                        ->rule(function (Get $get) {
                                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                $unite = UniteDeVente::find($get('unite_de_vente_id'));
                                                if ($unite && $unite->stock < $value) {
                                                    $fail("Quantité demandée ({$value}) supérieure au stock disponible ({$unite->stock}).");
                                                }
                                            };
                                        })
                                        ->live(onBlur: true)
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('prix_unitaire')
                                        ->label('Prix Unitaire (CFA)')
                                        ->numeric()
                                        ->required()
                                        ->live(onBlur: true)
                                        ->columnSpan(2),
                                ])
                                ->columns(8)
                                ->addActionLabel('Ajouter un produit'),
                        ]),

                    Forms\Components\Wizard\Step::make('Statut & Confirmation')
                        ->description('Vérifiez les détails et confirmez votre commande. Le changement de statut déclenchera la mise à jour du stock.')
                        ->schema([
                            Forms\Components\TextInput::make('numero_commande')
                                ->label('Numéro de Commande')
                                ->default('CMD-' . Str::upper(Str::random(8)))
                                ->disabled()
                                ->required()
                                ->unique(Order::class, 'numero_commande', ignoreRecord: true),

                            Forms\Components\Select::make('statut')
                                ->label('Statut de la commande')
                                ->options([
                                    'en_attente' => 'En attente',
                                    'validee' => 'Validée (déclenche transfert stock)',
                                    'prete_pour_livraison' => 'Prête pour livraison',
                                    'en_cours_de_livraison' => 'En cours de livraison',
                                    'livree' => 'Livrée',
                                    'annulee' => 'Annulée',
                                ])
                                ->required()
                                ->default('en_attente'),

                            Forms\Components\Textarea::make('notes')
                                ->label('Notes')
                                ->columnSpanFull(),
                        ]),
                ])->columnSpanFull()
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Forms\Components\Section::make('Résumé de la commande')
                    ->schema([
                        Forms\Components\TextEntry::make('numero_commande'),
                        Forms\Components\TextEntry::make('statut')->badge()->color(fn (string $state): string => match ($state) {
                            'en_attente' => 'warning',
                            'validee' => 'success',
                            'annulee' => 'danger',
                            default => 'gray',
                        }),
                        Forms\Components\TextEntry::make('created_at')->label('Date de création')->dateTime('d/m/Y H:i'),
                    ]),

                Forms\Components\Section::make('Client & Destination')
                    ->schema([
                        Forms\Components\TextEntry::make('client.nom'),
                        Forms\Components\TextEntry::make('pointDeVente.nom')->label('Point de Vente de destination'),
                    ]),

                Forms\Components\Section::make('Détail des produits')
                    ->schema([
                        Forms\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Forms\Components\TextEntry::make('uniteDeVente.nom_complet')->label('Produit')->columnSpan(2),
                                Forms\Components\TextEntry::make('quantite')->label('Quantité'),
                                Forms\Components\TextEntry::make('prix_unitaire')->label('Prix Unitaire')->money('XOF'),
                                Forms\Components\TextEntry::make('quantite')->label('Sous-total')->money('XOF')
                                    ->state(fn ($record) => $record->quantite * $record->prix_unitaire),
                            ])->columns(5),
                    ]),

                Forms\Components\Section::make('Montant & Paiement')
                    ->schema([
                        Forms\Components\TextEntry::make('montant_total')->label('Montant total')->money('XOF')->size('lg'),
                        Forms\Components\TextEntry::make('montant_paye')->label('Montant payé')->money('XOF')->color('success')->size('lg'),
                        Forms\Components\TextEntry::make('reste_a_payer')->label('Reste à payer')->money('XOF')
                            ->color(fn ($record) => $record->montant_total - $record->montant_paye > 0 ? 'warning' : 'success')
                            ->state(fn ($record) => $record->montant_total - $record->montant_paye)
                            ->size('lg'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_commande')->label('N° Commande')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('client.nom')->label('Client')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('pointDeVente.nom')->label('Point de Vente')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('statut')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en_attente' => 'warning',
                        'validee' => 'success',
                        'annulee' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('montant_total')->label('Montant Total')->numeric()->sortable()->money('XOF'),
                Tables\Columns\TextColumn::make('statut_paiement')->label('Paiement')->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'non_regle' => 'danger',
                        'partiellement_regle' => 'warning',
                        'completement_regle' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y')->sortable()->toggleable(true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Ajoutez les RelationManagers ici, ex :
            // ReglementsRelationManager::class,
        ];
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
