<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\PointDeVente;
use App\Models\UniteDeVente;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
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
use App\Services\StockManager;

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
                Section::make('Détails de la Commande')
                    ->schema([
                        // ... (champs existants)
                        TextInput::make('numero_commande')
                            ->default('CMD-' . random_int(1000, 9999))
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Select::make('client_id')
                            ->relationship('client', 'nom', fn (Builder $query) => $query->whereNotNull('nom'))
                            ->searchable()->preload()->live()
                            ->afterStateUpdated(fn (Set $set) => $set('point_de_vente_id', null))
                            ->required()->label('Client'),
                        Select::make('point_de_vente_id')
                            ->label('Point de Vente')
                            ->options(fn (Get $get): Collection => PointDeVente::query()
                                ->where('responsable_id', $get('client_id'))
                                ->pluck('nom', 'id'))
                            ->searchable()->preload()->required(),
                        DatePicker::make('created_at')
                            ->label('Date de commande')->default(now())->disabled(),
                    ])->columns(2),
                
                Section::make('Statut & Livraison')
                    ->schema([
                        Select::make('statut')
                            ->options([
                                'en_attente' => 'En attente',
                                'validee' => 'Validée (Transfert de stock)',
                                'en_preparation' => 'En préparation',
                                'en_cours_livraison' => 'En cours de livraison',
                                'livree' => 'Livrée',
                                'annulee' => 'Annulée',
                            ])
                            ->required()
                            ->live()
                            ->default('en_attente')
                            ->disabled(fn (?Order $record): bool => $record && $record->statut === 'validee')
                            ->afterStateUpdated(function (Set $set, Get $get, ?Order $record) {
                                // Ne pas ré-activer les champs si la commande a déjà été validée.
                                if ($record && $record->statut === 'validee') {
                                    $set('statut', 'validee');
                                    return;
                                }
                            })
                            ->rules(function (Get $get, ?Order $record) {
                                // Règle de validation personnalisée pour le stock
                                return [
                                    fn (string $attribute, $value, \Closure $fail) => $value === 'validee' && !$record?->exists ? self::validateStock($get, $fail) : null,
                                ];
                            })
                            ->dehydrated(fn (?string $state): bool => filled($state)),
                            
                        // ... (le reste du code du groupe "Livraison" est identique)
                        Group::make()
                            ->schema([
                                Select::make('livreur_id')
                                    ->relationship(name: 'livreur')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->prenom} {$record->nom}")
                                    ->searchable(['nom', 'prenom'])
                                    ->preload()
                                    ->label('Livreur à assigner'),
                            ])
                            ->visible(function (Get $get): bool {
                                $status = $get('statut');
                                return in_array($status, ['en_preparation', 'en_cours_livraison', 'livree']);
                            }),
                    ]),
                
                Section::make('Articles de la Commande')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                // ... (schéma existant)
                                Select::make('unite_de_vente_id')
                                    ->relationship('uniteDeVente', 'nom_unite', fn (Builder $query) => $query->whereNotNull('nom_unite'))
                                    ->searchable()->preload()->required()->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $unite = UniteDeVente::find($state);
                                        $set('prix_unitaire', $unite?->prix_unitaire ?? 0);
                                    })
                                    ->label('Produit (Unité de vente)'),
                                TextInput::make('quantite')
                                    ->numeric()->required()->live(onBlur: true)->default(1),
                                TextInput::make('prix_unitaire')
                                    ->numeric()->required()->live(onBlur: true),
                                Placeholder::make('prix_total_ligne')
                                    ->label('Total Ligne')
                                    ->content(fn (Get $get): string => number_format(($get('quantite') ?? 0) * ($get('prix_unitaire') ?? 0), 0, ',', ' ') . ' FCFA'),
                            ])
                            ->columns(4)
                            ->addActionLabel('Ajouter un article')
                            ->collapsible()->reorderable(false)->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateGrandTotal($get, $set))
                            ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action->after(fn (Get $get, Set $set) => self::updateGrandTotal($get, $set))),
                    ]),
                
                Section::make('Résumé Financier et Notes')
                    ->schema([
                        TextInput::make('montant_total')
                            ->numeric()->readOnly()->prefix('FCFA')
                            ->label('Montant Total de la Commande'),
                        Textarea::make('notes')->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Valide si le stock est suffisant pour les articles de la commande.
     */
    public static function validateStock(Get $get, \Closure $fail): void
    {
        $items = $get('items');
        if (empty($items)) {
            $fail("La commande ne contient aucun article.");
            return;
        }

        $stockManager = app(StockManager::class);
        $erreurs = [];

        foreach ($items as $item) {
            $uniteDeVenteId = $item['unite_de_vente_id'] ?? null;
            $quantiteDemandee = $item['quantite'] ?? 0;
            
            if ($uniteDeVenteId && $quantiteDemandee > 0) {
                $unite = UniteDeVente::find($uniteDeVenteId);
                $stockDisponible = $stockManager->getInventoryStock($unite, null); // null pour l'entrepôt principal
                
                if ($stockDisponible < $quantiteDemandee) {
                    $erreurs[] = "Stock insuffisant pour l'article '{$unite->nom_unite}' (disponible: {$stockDisponible}, demandé: {$quantiteDemandee})";
                }
            }
        }

        if (!empty($erreurs)) {
            foreach ($erreurs as $erreur) {
                $fail($erreur);
            }
        }
    }

    // ... (le reste des méthodes `table`, `updateGrandTotal`, `getRelations`, `getPages` restent identiques)
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_commande')->searchable()->sortable(),
                TextColumn::make('client.nom')->searchable()->sortable(),
                TextColumn::make('pointDeVente.nom')->label('Point de Vente'),
                BadgeColumn::make('statut')
                    ->colors([
                        'warning' => fn ($state) => in_array($state, ['en_attente', 'en_preparation']),
                        'success' => 'livree',
                        'info' => 'en_cours_livraison',
                        'primary' => 'validee', 
                        'danger' => 'annulee',
                    ])->sortable(),
                BadgeColumn::make('statut_paiement')
                    ->colors([
                        'danger' => 'non_payee',
                        'warning' => 'Partiellement réglé',
                        'success' => 'Complètement réglé',
                    ])->sortable(),
                TextColumn::make('montant_total')->money('XOF')->sortable(),
                TextColumn::make('created_at')->dateTime('d/m/Y')->label('Date')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('invoice')
                    ->label('Facture')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Order $record) => route('invoice.order', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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