<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockTransfertResource\Pages;
use App\Models\Inventory;
use App\Models\LieuDeStockage;
use App\Models\PointDeVente;
use App\Models\StockTransfert;
use App\Models\UniteDeVente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockTransfertResource extends Resource
{
    protected static ?string $model = StockTransfert::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Gestion de Stock';
    protected static ?string $navigationLabel = 'Transferts de Stock';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('Détails du Transfert')
                ->schema([
                    // -- SOURCE (seulement les points de vente) --
                    Forms\Components\Select::make('source_id')
                        ->label('Source (Point de Vente d\'origine)')
                        ->options(PointDeVente::query()->pluck('nom', 'id'))
                        ->searchable()->preload()->live()
                        ->required()
                        // On définit implicitement le type comme 'point_de_vente'
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('source_type', 'point_de_vente')),

                    // -- DESTINATION (un autre point de vente OU l'entrepôt) --
                    Forms\Components\Select::make('destination_type')
                        ->label('Destination (Où va le stock ?)')
                        ->options([
                            'point_de_vente' => 'Vers un autre Point de Vente',
                            'entrepot' => 'Retour à l\'Entrepôt Principal',
                        ])
                        ->required()->live()->afterStateUpdated(fn (Forms\Set $set) => $set('destination_id', null)),

                    Forms\Components\Select::make('destination_id')
                        ->label('Point de Vente Destination')
                        ->options(function (Get $get) {
                            // On ne peut pas transférer vers soi-même.
                            return PointDeVente::query()
                                ->where('id', '!=', $get('source_id'))
                                ->pluck('nom', 'id');
                        })
                        ->searchable()->preload()
                        ->visible(fn (Get $get) => $get('destination_type') === 'point_de_vente')
                        ->required(fn (Get $get) => $get('destination_type') === 'point_de_vente'),

                ])->columns(2),
                
                Forms\Components\Section::make('Articles à Transférer')
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('unite_de_vente_id')
                                    ->label('Article à transférer')
                                    ->options(function (Get $get) {
                                        $sourceType = $get('../../source_type');
                                        $sourceId = $get('../../source_id');
                                        if (!$sourceType) return [];

                                        $lieu = self::getLieuDeStockage($sourceType, $sourceId);
                                        if (!$lieu) return [];
                                        
                                        // On ne montre que les produits qui ont du stock à la source.
                                        return $lieu->inventories()
                                            ->where('quantite_stock', '>', 0)
                                            ->with('uniteDeVente')
                                            ->get()
                                            ->mapWithKeys(function ($inventory) {
                                                return [$inventory->unite_de_vente_id => $inventory->uniteDeVente->nom_complet . ' (Stock: ' . $inventory->quantite_stock . ')'];
                                            });
                                    })
                                    ->required()->searchable()->live()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                
                                Forms\Components\TextInput::make('quantite')
                                    ->numeric()->required()->minValue(1)
                                    ->rules([
                                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $sourceType = $get('../../source_type');
                                            $sourceId = $get('../../source_id');
                                            $uniteDeVenteId = $get('unite_de_vente_id');

                                            $lieu = self::getLieuDeStockage($sourceType, $sourceId);
                                            $stockDisponible = $lieu?->inventories()->where('unite_de_vente_id', $uniteDeVenteId)->value('quantite_stock') ?? 0;

                                            if ($value > $stockDisponible) {
                                                $fail("La quantité à transférer ({$value}) dépasse le stock disponible ({$stockDisponible}).");
                                            }
                                        },
                                    ]),
                            ])->columns(2)
                    ])->hidden(fn (Get $get) => !$get('source_type')),

                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('source.nom')->label('De')->badge()->searchable(),
                Tables\Columns\TextColumn::make('destination.nom')->label('Vers')->badge()->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Effectué par')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
    
    // Helper pour trouver le lieu de stockage dans le formulaire.
    private static function getLieuDeStockage(string $type, ?int $id): ?LieuDeStockage
    {
        if ($type === 'entrepot') {
            $entrepotId = cache()->get('entrepot_principal_id');
            return LieuDeStockage::find($entrepotId);
        }

        if ($type === 'point_de_vente' && $id) {
            return PointDeVente::find($id)?->lieuDeStockage;
        }

        return null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockTransferts::route('/'),
            'create' => Pages\CreateStockTransfert::route('/create'),
            // 'view' => Pages\ViewStockTransfert::route('/{record}'),
        ];
    }
}