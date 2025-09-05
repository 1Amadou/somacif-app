<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockTransfertResource\Pages;
use App\Models\Inventory;
use App\Models\PointDeVente;
use App\Models\StockTransfert;
use App\Services\StockManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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
                        Forms\Components\Select::make('source_point_de_vente_id')
                            ->label('Point de Vente Source (D\'où vient le stock ?)')
                            ->relationship('sourcePointDeVente', 'nom')
                            ->searchable()->preload()->live()
                            ->required(),

                        Forms\Components\Select::make('destination_point_de_vente_id')
                            ->label('Point de Vente Destination (Où va le stock ?)')
                            ->options(function (Get $get) {
                                $pointsDeVente = PointDeVente::where('id', '!=', $get('source_point_de_vente_id'))->pluck('nom', 'id');
                                return collect(['null' => '--- RETOUR À L\'ENTREPÔT PRINCIPAL ---'])->union($pointsDeVente);
                            })
                            ->searchable()->required()
                            ->different('source_point_de_vente_id'),
                    ])->columns(2),

                // --- NOUVELLE SECTION DYNAMIQUE ---
                Forms\Components\Section::make('Articles à Transférer')
                    ->description('Saisissez les quantités à transférer pour chaque article.')
                    ->schema([
                        // Ce composant va afficher notre liste de produits personnalisée
                        Forms\Components\View::make('filament.forms.components.stock-transfer-repeater')
                            ->hidden(fn (Get $get) => !$get('source_point_de_vente_id')), // N'apparaît que si une source est choisie
                    ]),
                    
                Forms\Components\Textarea::make('notes')
                    ->label('Notes sur le transfert (facultatif)')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('sourcePointDeVente.nom')->label('De')->badge()->searchable(),
                Tables\Columns\TextColumn::make('destinationPointDeVente.nom')->label('Vers')->badge()->default('Entrepôt Principal')->searchable(),
                Tables\Columns\TextColumn::make('articles_count')
                    ->label('Articles')
                    ->state(function (Model $record): int {
                        return count($record->details ?? []);
                    }),
                Tables\Columns\TextColumn::make('user.name')->label('Effectué par')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
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