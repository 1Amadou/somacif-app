<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ReglementsRelationManager;
use App\Models\Client;
use App\Models\Order;
use App\Models\PointDeVente;
use App\Models\UniteDeVente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Ventes & Commandes';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Commande';


    public static function form(Form $form): Form
    {
        // Le formulaire est déjà stable et correct
        return $form
            ->schema([
                Forms\Components\Section::make('Détails de la Commande')
                    ->schema([
                        Forms\Components\TextInput::make('numero_commande')->default(fn () => strtoupper(uniqid('CMD-')))->disabled()->dehydrated()->required(),
                        Forms\Components\Select::make('client_id')->relationship('client', 'nom')->searchable()->preload()->live()->afterStateUpdated(fn (Set $set) => $set('point_de_vente_id', null))->required()->label('Client'),
                        Forms\Components\Select::make('point_de_vente_id')->label('Point de Vente de Destination')->options(fn (Get $get): Collection => PointDeVente::query()->where('responsable_id', $get('client_id'))->pluck('nom', 'id'))->searchable()->preload()->required(),
                    ])->columns(2),
                Forms\Components\Section::make('Statut & Livraison')->schema([
                    Forms\Components\Select::make('statut')->options(collect(OrderStatusEnum::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()]))->required()->live()->default(OrderStatusEnum::EN_ATTENTE->value),
                    Forms\Components\Select::make('livreur_id')->relationship('livreur', 'nom')->getOptionLabelFromRecordUsing(fn ($record) => "{$record->prenom} {$record->nom}")->searchable(['nom', 'prenom'])->preload()->label('Livreur à assigner'),
                ]),
                Forms\Components\Section::make('Articles de la Commande')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
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
                                Forms\Components\Placeholder::make('prix_total_ligne')->label('Total Ligne')->content(fn (Get $get): string => number_format(($get('quantite') ?? 0) * ($get('prix_unitaire') ?? 0), 0, ',', ' ') . ' FCFA'),
                            ])
                            ->columns(4)->addActionLabel('Ajouter un article')->collapsible()->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateGrandTotal($get, $set))
                            ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action->after(fn (Get $get, Set $set) => self::updateGrandTotal($get, $set))),
                    ]),
                Forms\Components\Section::make('Résumé')->schema([
                    Forms\Components\TextInput::make('montant_total')->numeric()->readOnly()->prefix('FCFA')->label('Montant Total'),
                    Forms\Components\Textarea::make('notes')->columnSpanFull(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        // La table est déjà stable et correcte
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_commande')->searchable()->sortable()->label('N° Commande'),
                Tables\Columns\TextColumn::make('client.nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('statut')->label('Statut Livraison')->badge()->color(fn (OrderStatusEnum $state): string => $state->getColor())->formatStateUsing(fn (OrderStatusEnum $state): string => $state->getLabel())->sortable(),
                Tables\Columns\TextColumn::make('statut_paiement')->label('Statut Paiement')->badge()->color(fn (PaymentStatusEnum $state): string => $state->getColor())->formatStateUsing(fn (PaymentStatusEnum $state): string => $state->getLabel())->sortable(),
                Tables\Columns\TextColumn::make('solde_restant_a_payer')->money('XOF')->label('Solde Restant')->color('warning')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('client_id')->label('Client')->options(Client::pluck('nom', 'id')->all())->searchable(),
                SelectFilter::make('statut')->label('Statut de Livraison')->options(collect(OrderStatusEnum::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])),
                SelectFilter::make('statut_paiement')->label('Statut de Paiement')->options(collect(PaymentStatusEnum::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])),
                TernaryFilter::make('is_vente_directe')->label('Type de Vente')->placeholder('Toutes')->trueLabel('Vente Directe')->falseLabel('Bon de Commande'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    /**
     * CORRECTION MAJEURE : La définition complète de la vue de détail est ici.
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Situation de la Commande')
                ->schema([
                    Infolists\Components\Grid::make(3)->schema([
                        Infolists\Components\TextEntry::make('quantite_actuelle')->label('Cartons sur la Commande'),
                        Infolists\Components\TextEntry::make('quantite_reglee')->label('Cartons Réglés')->color('success'),
                        Infolists\Components\TextEntry::make('remise_totale')->label('Remise/Surprix Réalisé')->money('XOF')->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                    ]),
                ])->columnSpan(2),
            
            Infolists\Components\Section::make('Suivi des Quantités')
                ->schema([
                    Infolists\Components\TextEntry::make('quantite_initiale')->label('Quantité Initiale'),
                    Infolists\Components\TextEntry::make('quantite_transferee')->label('Quantité Transférée')->color('warning'),
                ])->columnSpan(1),

            Infolists\Components\Section::make('Détails Financiers')
                ->schema([
                    Infolists\Components\Grid::make(3)->schema([
                        Infolists\Components\TextEntry::make('montant_total')->label('Montant Proforma')->money('XOF'),
                        Infolists\Components\TextEntry::make('total_verse')->label('Total Encaissé')->money('XOF')->color('success'),
                        Infolists\Components\TextEntry::make('statut_paiement')->label('Statut Paiement')->badge()->color(fn ($state) => $state->getColor())->formatStateUsing(fn ($state) => $state->getLabel()),
                    ]),
                ])->columnSpan('full'),
            
            Infolists\Components\Section::make('Articles Commandés')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('items')
                        ->schema([
                            Infolists\Components\TextEntry::make('uniteDeVente.nom_complet')->label('Article')->columnSpan(2),
                            Infolists\Components\TextEntry::make('quantite'),
                            Infolists\Components\TextEntry::make('prix_unitaire')->money('XOF'),
                            Infolists\Components\TextEntry::make('total_ligne')->money('XOF')->label('Total'),
                        ])->columns(4)->label(''),
                ])->columnSpan('full'),
        ])->columns(3);
    }
 
    public static function updateGrandTotal(Get $get, Set $set): void
    {
        $total = collect($get('items'))->sum(fn (array $item) => ($item['quantite'] ?? 0) * ($item['prix_unitaire'] ?? 0));
        $set('montant_total', $total);
    }
    
    public static function getRelations(): array
    {
        return [
            ReglementsRelationManager::class,
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

