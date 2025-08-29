<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

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
                    Forms\Components\Tabs::make('Product Tabs')->tabs([
                        Forms\Components\Tabs\Tab::make('Informations Principales')->schema([
                            Forms\Components\TextInput::make('nom')->required()->live(onBlur: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                            Forms\Components\MarkdownEditor::make('description_courte')->columnSpanFull(),
                            Forms\Components\RichEditor::make('description_longue')->label('Description longue')->columnSpanFull(),
                        ])->columns(2),

                        // Onglets pour les détails supplémentaires (si tu en as besoin)
                        // Tu peux décommenter et adapter cette section
                        
                        Forms\Components\Tabs\Tab::make('Détails & Recettes')->schema([
                            Forms\Components\TextInput::make('origine'),
                            Forms\Components\TextInput::make('poids_moyen'),
                            Forms\Components\TextInput::make('conservation'),
                            Forms\Components\RichEditor::make('infos_nutritionnelles')->label('Informations Nutritionnelles')->columnSpanFull(),
                            Forms\Components\RichEditor::make('idee_recette')->label('Idée Recette')->columnSpanFull(),
                        ])->columns(3),
                      
                    ]),
                ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Statut et Images')->schema([
                        Forms\Components\Toggle::make('is_visible')->label('Visible sur le site')->default(true),
                        Forms\Components\FileUpload::make('image_principale')->image()->imageEditor()->disk('public')->directory('products'),
                        // CHAMP GALERIE D'IMAGES CORRIGÉ ET RÉINTÉGRÉ
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
                // Compte le nombre de calibres/unités associés à ce produit
                Tables\Columns\TextColumn::make('uniteDeVentes_count')->label('Calibres')->counts('uniteDeVentes'),
                // Affiche la somme des stocks de toutes ses unités de vente
                Tables\Columns\TextColumn::make('uniteDeVentes_sum_stock')
                    ->sum('uniteDeVentes', 'stock')
                    ->label('Stock Total')
                    ->numeric()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        // On s'assure d'utiliser notre nouveau manager de relation pour les unités de vente
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