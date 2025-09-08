<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VenteDirecteResource\Pages;
use App\Models\UniteDeVente;
use App\Models\VenteDirecte;
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

class VenteDirecteResource extends Resource
{
    protected static ?string $model = VenteDirecte::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Ventes en Gros';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = 'Vente Directe';
    protected static ?string $pluralLabel = 'Ventes Directes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations sur la Vente')
                    ->schema([
                        Forms\Components\TextInput::make('numero_facture')
                            ->default('FD-' . random_int(100000, 999999))
                            ->disabled()->dehydrated()->required(),
                        Forms\Components\Select::make('client_id')
                            ->relationship('client', 'nom', fn (Builder $query) => $query->whereNotNull('nom'))
                            ->searchable()->preload()->required()->label('Client'),
                        Forms\Components\DatePicker::make('date_vente')
                            ->default(now())->required()->label('Date de la vente'),
                    ])->columns(3),

                Forms\Components\Section::make('Articles Vendus (depuis l\'Entrepôt Principal)')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('unite_de_vente_id')
                                    ->label('Produit (Unité de vente)')
                                    ->options(function () {
                                        // On ne montre que les produits avec un stock positif à l'entrepôt.
                                        return UniteDeVente::query()
                                            ->get()
                                            ->filter(fn ($unite) => $unite->stock_entrepôt_principal > 0)
                                            ->mapWithKeys(function ($unite) {
                                                // *** CORRECTION CLÉ 1 ***
                                                // On utilise le nouvel accesseur 'stock_entrepôt_principal'
                                                return [$unite->id => $unite->nom_complet . ' (Stock: ' . $unite->stock_entrepôt_principal . ')'];
                                            });
                                    })
                                    ->searchable()->preload()->required()->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $unite = UniteDeVente::find($state);
                                        $set('prix_unitaire', $unite?->prix_particulier ?? 0);
                                    }),
                                Forms\Components\TextInput::make('quantite')
                                    ->numeric()->required()->live(onBlur: true)->default(1)
                                    // *** CORRECTION CLÉ 2 ***
                                    // Règle de validation en temps réel
                                    ->rules([
                                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $unite = UniteDeVente::find($get('unite_de_vente_id'));
                                            if (!$unite) return;
                                            
                                            if ($value > $unite->stock_entrepôt_principal) {
                                                $fail("Le stock est insuffisant. Disponible : {$unite->stock_entrepôt_principal}.");
                                            }
                                        },
                                    ]),
                                Forms\Components\TextInput::make('prix_unitaire')
                                    ->numeric()->required()->live(onBlur: true),
                                Forms\Components\Placeholder::make('total_ligne')
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
                            ->numeric()->readOnly()->prefix('FCFA')->label('Montant Total de la Vente'),
                        Forms\Components\Textarea::make('notes')->columnSpanFull(),
                    ]),
            ]);
    }
    
    // Le reste du fichier (infolist, table, etc.) reste identique.

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations sur la Vente')->schema([
                    Infolists\Components\TextEntry::make('numero_facture'),
                    Infolists\Components\TextEntry::make('client.nom'),
                    Infolists\Components\TextEntry::make('date_vente')->date('d/m/Y'),
                ])->columns(3),
                Infolists\Components\Section::make('Détail des Articles Vendus')->schema([
                    Infolists\Components\RepeatableEntry::make('items')
                        ->schema([
                            Infolists\Components\TextEntry::make('uniteDeVente.nom_complet')->label('Article'),
                            Infolists\Components\TextEntry::make('quantite')->label('Quantité'),
                            Infolists\Components\TextEntry::make('prix_unitaire')->money('XOF')->label('Prix Unitaire'),
                        ])->columns(3)->label(''),
                ]),
                Infolists\Components\Section::make('Résumé')->schema([
                    Infolists\Components\TextEntry::make('montant_total')->money('XOF')->label('Montant Total'),
                    Infolists\Components\TextEntry::make('notes')->markdown()->label('Notes'),
                ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_facture')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('client.nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('date_vente')->date('d/m/Y')->label('Date')->sortable(),
                Tables\Columns\TextColumn::make('montant_total')->money('XOF')->sortable(),
            ])
            ->defaultSort('date_vente', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function updateGrandTotal(Get $get, Set $set): void
    {
        $total = collect($get('items'))->sum(function (array $item) {
            return ($item['quantite'] ?? 0) * ($item['prix_unitaire'] ?? 0);
        });
        $set('montant_total', $total);
    }
    
    public static function getRelations(): array
    {
        return [];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVenteDirectes::route('/'),
            'create' => Pages\CreateVenteDirecte::route('/create'),
            'edit' => Pages\EditVenteDirecte::route('/{record}/edit'),
            'view' => Pages\ViewVenteDirecte::route('/{record}'),
        ];
    }
}