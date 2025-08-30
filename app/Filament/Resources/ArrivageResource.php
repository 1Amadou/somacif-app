<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArrivageResource\Pages;
use App\Models\Arrivage;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArrivageResource extends Resource
{
    protected static ?string $model = Arrivage::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationGroup = 'Gestion de Stock';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations sur l\'Arrivage')
                    ->description('Détails principaux de la réception de marchandise.')
                    ->schema([
                        Select::make('fournisseur_id')
                            ->relationship(
                                name: 'fournisseur',
                                titleAttribute: 'nom_entreprise',
                                // ROBUSTESSE : Ignore les fournisseurs sans nom pour éviter les erreurs.
                                modifyQueryUsing: fn (Builder $query) => $query->whereNotNull('nom_entreprise')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Fournisseur'),
                        
                        TextInput::make('numero_bon_livraison')
                            ->required()
                            ->maxLength(255)
                            ->label('Numéro du Bon de Livraison'),

                        DatePicker::make('date_arrivage')
                            ->required()
                            ->default(now())
                            ->label('Date de l\'arrivage'),
                    ])->columns(2),

                Section::make('Détails des Produits Reçus')
                    ->schema([
                        Repeater::make('details_produits')
                            ->label('Produits')
                            ->schema([
                                Select::make('unite_de_vente_id')
                                    ->label('Unité de Vente')
                                    ->options(UniteDeVente::query()->pluck('nom_unite', 'id'))
                                    ->searchable()
                                    ->required()
                                    // ROBUSTESSE : On s'assure que l'option sélectionnée est valide.
                                    ->exists('unite_de_ventes', 'id'),

                                TextInput::make('quantite')
                                    ->label('Quantité (en cartons/unités)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->live(onBlur: true), // 'live' met à jour les calculs en temps réel
                            ])
                            ->addActionLabel('Ajouter un produit')
                            ->columns(2)
                            // Calcul automatique du total
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            })
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action->after(fn (Get $get, Set $set) => self::updateTotals($get, $set)),
                            )
                            ->reorderable(false)
                            ->collapsible(),
                    ]),
                
                Section::make('Résumé et Notes')
                    ->schema([
                        // CHAMP CALCULE : Affiche le total des cartons pour vérification.
                        TextInput::make('total_quantite')
                            ->label('Quantité Totale Reçue')
                            ->numeric()
                            ->readOnly()
                            ->default(0),

                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fournisseur.nom_entreprise')
                    ->searchable()
                    ->sortable()
                    ->label('Fournisseur'),
                TextColumn::make('numero_bon_livraison')
                    ->searchable()
                    ->label('N° Bon Livraison'),
                TextColumn::make('date_arrivage')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Date'),
                // CLARTE : Affiche le nombre total de cartons directement dans la table.
                TextColumn::make('total_cartons')
                    ->label('Quantité Totale')
                    ->state(function (Arrivage $record): int {
                        return collect($record->details_produits)->sum('quantite');
                    }),
                TextColumn::make('user.name')
                    ->label('Enregistré par')
                    ->sortable(),
            ])
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

    // FONCTION UTILITAIRE : Centralise le calcul du total.
    public static function updateTotals(Get $get, Set $set): void
    {
        $details = $get('details_produits');
        $totalQuantite = 0;

        if (is_array($details)) {
            $totalQuantite = collect($details)->pluck('quantite')->sum();
        }

        $set('total_quantite', $totalQuantite);
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
            'index' => Pages\ListArrivages::route('/'),
            'create' => Pages\CreateArrivage::route('/create'),
            'edit' => Pages\EditArrivage::route('/{record}/edit'),
            'view' => Pages\ViewArrivage::route('/{record}'),
        ];
    }    
}