<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers; // Important
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Filament\Forms\Set;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withSum('pointsDeVenteStock', 'inventory.quantite_stock');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Tabs::make('Product Tabs')->tabs([
                        Forms\Components\Tabs\Tab::make('Informations Principales')->schema([
                            Forms\Components\TextInput::make('nom')->required()->live(onBlur: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                            Forms\Components\TextInput::make('slug')->required()->unique(Product::class, 'slug', ignoreRecord: true),
                            Forms\Components\TagsInput::make('calibres')->label('Calibres disponibles'),
                            Forms\Components\Textarea::make('description_courte')->columnSpanFull(),
                            Forms\Components\RichEditor::make('description_longue')->label('Description longue')->columnSpanFull(),
                        ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Détails & Recettes')->schema([
                            Forms\Components\TextInput::make('origine'),
                            Forms\Components\TextInput::make('poids_moyen'),
                            Forms\Components\TextInput::make('conservation'),
                            Forms\Components\RichEditor::make('infos_nutritionnelles')->label('Informations Nutritionnelles')->columnSpanFull(),
                            Forms\Components\RichEditor::make('idee_recette')->label('Idée Recette')->columnSpanFull(),
                        ])->columns(3),
                        
                        Forms\Components\Tabs\Tab::make('Unités de Vente & Prix')->schema([
                            Forms\Components\Repeater::make('uniteDeVentes')->relationship()->schema([
                                Forms\Components\TextInput::make('nom_unite')->required(),
                                Forms\Components\TextInput::make('prix_grossiste')->required()->numeric(),
                                Forms\Components\TextInput::make('prix_hotel_restaurant')->required()->numeric(),
                                Forms\Components\TextInput::make('prix_particulier')->required()->numeric(),
                            ])->columns(4),
                        ]),
                    ]),
                ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Statut et Images')->schema([
                        Forms\Components\Toggle::make('is_visible')->label('Visible sur le site')->default(true),
                        Forms\Components\FileUpload::make('image_principale')->image()->imageEditor()->disk('public')->directory('products'),
                        Forms\Components\FileUpload::make('images_galerie')
                            ->label('Galerie d\'images')
                            ->multiple()
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->reorderable(),
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
                Tables\Columns\TextColumn::make('points_de_vente_stock_sum_quantite_stock')->label('Stock Total')->numeric()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    // ON DÉCLARE LE NOUVEAU RELATION MANAGER ICI
    public static function getRelations(): array
    {
        return [
            RelationManagers\PointsDeVenteStockRelationManager::class,
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