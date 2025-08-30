<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReglementResource\Pages;
use App\Models\Client;
use App\Models\Order;
use App\Models\Reglement;
use App\Models\UniteDeVente;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
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

class ReglementResource extends Resource
{
    protected static ?string $model = Reglement::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Ventes & Commandes';
    protected static ?int $navigationSort = 2;
    protected static ?string $label = 'Règlement Client';
    protected static ?string $pluralLabel = 'Règlements Clients';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations du Règlement')
                    ->schema([
                        Select::make('client_id')
                            ->relationship('client', 'nom', fn (Builder $query) => $query->whereNotNull('nom'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live() // Indispensable pour filtrer les commandes
                            ->afterStateUpdated(function (Set $set) {
                                // Réinitialise les champs dépendants
                                $set('orders', []);
                                $set('montant_calcule', 0);
                            })
                            ->label('Client'),

                        DatePicker::make('date_reglement')
                            ->required()
                            ->default(now())
                            ->label('Date du règlement'),

                        TextInput::make('montant_verse')
                            ->numeric()
                            ->required()
                            ->prefix('FCFA')
                            ->label('Montant Versé'),
                        
                        // CHAMP CALCULE : Montant total des commandes sélectionnées
                        TextInput::make('montant_calcule')
                            ->numeric()
                            ->readOnly()
                            ->prefix('FCFA')
                            ->label('Montant Calculé (dû)'),

                        Select::make('methode_paiement')
                            ->options([
                                'especes' => 'Espèces',
                                'cheque' => 'Chèque',
                                'virement' => 'Virement bancaire',
                                'mobile' => 'Mobile Money',
                            ])
                            ->required()
                            ->label('Méthode de Paiement'),

                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Section::make('Commandes Associées')
                    ->description('Sélectionnez les commandes que ce règlement concerne.')
                    ->collapsible()
                    ->schema([
                        // FORMULAIRE INTELLIGENT : Ne montre que les commandes non réglées du client.
                        Select::make('orders')
                            ->label('Commandes à Régler')
                            ->multiple()
                            ->relationship('orders', 'numero_commande')
                            ->options(function (Get $get): Collection {
                                $clientId = $get('client_id');
                                if (!$clientId) {
                                    return collect();
                                }
                                return Order::query()
                                    ->where('client_id', $clientId)
                                    ->whereIn('statut_paiement', ['non_payee', 'Partiellement réglé'])
                                    ->pluck('numero_commande', 'id');
                            })
                            ->preload()
                            ->live()
                            // Met à jour le montant calculé automatiquement
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $total = Order::whereIn('id', $state)->sum('montant_total');
                                $set('montant_calcule', $total);
                            }),
                    ]),

                Section::make('Détails des Ventes (Déstockage)')
                    ->description('Déclarez ici les articles vendus par le client pour déduire son inventaire.')
                    ->collapsible()
                    ->schema([
                        Repeater::make('details')
                            ->relationship()
                            ->schema([
                                Select::make('unite_de_vente_id')
                                    ->relationship('uniteDeVente', 'nom_unite', fn (Builder $query) => $query->whereNotNull('nom_unite'))
                                    ->searchable()->preload()->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $unite = UniteDeVente::find($state);
                                        $set('prix_de_vente_unitaire', $unite?->prix_unitaire ?? 0);
                                    })
                                    ->label('Produit Vendu'),
                                
                                TextInput::make('quantite_vendue')
                                    ->numeric()->required()->live(onBlur: true)->default(1)->label('Quantité Vendue'),
                                
                                TextInput::make('prix_de_vente_unitaire')
                                    ->numeric()->required()->label('Prix Unitaire'),
                            ])
                            ->addActionLabel('Ajouter un article vendu')
                            ->columns(3)
                            ->reorderable(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.nom')->searchable()->sortable(),
                TextColumn::make('date_reglement')->date('d/m/Y')->sortable(),
                TextColumn::make('montant_verse')->money('XOF')->sortable(),
                // CLARTE : Affiche les numéros de commande associés.
                TextColumn::make('orders.numero_commande')
                    ->badge()
                    ->label('Commandes Réglées'),
                TextColumn::make('methode_paiement')->searchable()->sortable(),
                TextColumn::make('user.name')->label('Enregistré par')->sortable(),
            ])
            ->defaultSort('date_reglement', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReglements::route('/'),
            'create' => Pages\CreateReglement::route('/create'),
            'edit' => Pages\EditReglement::route('/{record}/edit'),
        ];
    }    
}