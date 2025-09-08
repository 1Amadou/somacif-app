<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Produits';
    protected static ?string $navigationGroup = 'Stock & Catalogue';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Tabs::make('Product Details')->tabs([
                        Forms\Components\Tabs\Tab::make('Informations Principales')->schema([
                            Forms\Components\TextInput::make('nom')
                                ->required()
                                ->live(onBlur: true)
                                ->unique(ignoreRecord: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                            Forms\Components\TextInput::make('slug')
                                ->required()
                                ->unique(ignoreRecord: true),
                            Forms\Components\MarkdownEditor::make('description_courte')
                                ->label('Description Courte')
                                ->columnSpanFull(),
                            Forms\Components\RichEditor::make('description_longue')
                                ->label('Description Longue')
                                ->columnSpanFull(),
                        ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Détails & Recettes')->schema([
                            Forms\Components\TextInput::make('origine'),
                            Forms\Components\TextInput::make('poids_moyen'),
                            Forms\Components\TextInput::make('conservation'),
                            Forms\Components\RichEditor::make('infos_nutritionnelles')->columnSpanFull(),
                            Forms\Components\RichEditor::make('idee_recette')->columnSpanFull(),
                        ])->columns(3),
                    ]),
                ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Statut et Images')->schema([
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Visible sur la boutique')
                            ->default(true),
                        Forms\Components\FileUpload::make('image_principale')
                            ->label('Image Principale')
                            ->image()->imageEditor()->disk('public')->directory('products')
                            ->nullable(),
                        Forms\Components\FileUpload::make('images_galerie')
                            ->label('Galerie d\'images')
                            ->multiple()->image()->imageEditor()->disk('public')->directory('products')->reorderable()
                            ->nullable(),
                    ]),
                    Forms\Components\Section::make('SEO')->schema([
                        Forms\Components\TextInput::make('meta_titre'),
                        Forms\Components\Textarea::make('meta_description'),
                    ]),
                ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\ImageColumn::make('image_principale')->label('Image'),
            Tables\Columns\TextColumn::make('nom')->searchable()->sortable(),
            Tables\Columns\IconColumn::make('is_visible')->label('Visibilité')->boolean(),
            Tables\Columns\TextColumn::make('uniteDeVentes_count')->counts('uniteDeVentes')->label('Unités de Vente'),

            Tables\Columns\TextColumn::make('stock_total_entrepot')
                ->label('Stock Total (Entrepôt Principal)')
                ->state(function (Product $record): string {
                    $stock = $record->uniteDeVentes->sum('stock_entrepôt_principal');
                    return number_format($stock, 0, '', ' ');
                })
                // *** LA CORRECTION EST ICI ***
                // On explique à Filament comment trier cette colonne calculée.
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    $entrepotId = cache()->get('entrepot_principal_id');
                    if (!$entrepotId) {
                        return $query; // Ne rien faire si l'entrepôt n'est pas trouvé
                    }
                    
                    // On ajoute une sous-requête pour calculer la somme des stocks
                    // et on trie en fonction de cette somme.
                    return $query
                        ->withSum(['uniteDeVentes as stock_sum' => function (Builder $query) use ($entrepotId) {
                            $query->join('inventories', 'unite_de_ventes.id', '=', 'inventories.unite_de_vente_id')
                                ->where('inventories.lieu_de_stockage_id', $entrepotId);
                        }], 'inventories.quantite_stock')
                        ->orderBy('stock_sum', $direction);
                }),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}

    protected static function beforeDelete(Model $record): void
    {
        if ($record->uniteDeVentes()->exists()) {
            Notification::make()
                ->title('Suppression impossible')
                ->body('Ce produit ne peut pas être supprimé car des Unités de Vente y sont associées.')
                ->danger()
                ->send();

            self::halt();
        }
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UniteDeVentesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}