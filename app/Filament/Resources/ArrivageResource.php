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
                            ->relationship('fournisseur', 'nom_entreprise')
                            ->searchable()->preload()->required()->label('Fournisseur'),
                        
                        TextInput::make('numero_bon_livraison')
                            ->required()->unique(ignoreRecord: true)->maxLength(255)
                            ->label('Numéro du Bon de Livraison'),

                        DatePicker::make('date_arrivage')
                            ->required()->default(now())->label('Date de l\'arrivage'),
                    ])->columns(3),

                Section::make('Détails des Produits Reçus')
                    ->schema([
                        // *** MODIFICATION TECHNIQUE IMPORTANTE ***
                        // On renomme le repeater en 'items' pour qu'il corresponde
                        // au nom de la relation Eloquent ('items') dans le modèle Arrivage.
                        // Cela simplifie la communication avec notre ArrivageObserver.
                        Repeater::make('items')
                            ->relationship() // On lie directement le repeater à la relation
                            ->label('Produits')
                            ->schema([
                                Select::make('unite_de_vente_id')
                                    ->label('Unité de Vente')
                                    // Utilise 'nom_complet' pour un affichage sans ambiguïté.
                                    ->options(UniteDeVente::query()->pluck('nom_complet', 'id'))
                                    ->searchable()->required()->live(onBlur: true)
                                    // Empêche de sélectionner deux fois le même article.
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                
                                TextInput::make('quantite')
                                    ->label('Quantité Reçue')
                                    ->numeric()->required()->minValue(1)->live(onBlur: true),
                                
                                TextInput::make('prix_achat_unitaire')
                                    ->label('Coût d\'Achat Unitaire')
                                    ->prefix('FCFA')->numeric()->required()->live(onBlur: true),
                            ])
                            ->columns(3)
                            ->addActionLabel('Ajouter un produit')
                            ->reorderable(true)->collapsible()
                            ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action->after(fn (Get $get, Set $set) => self::updateTotals($get, $set)))
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            }),
                    ]),
                
                Section::make('Résumé et Notes')
                    ->schema([
                        TextInput::make('montant_total_arrivage')
                            ->label('Coût Total de l\'Arrivage')
                            ->prefix('FCFA')->numeric()->readOnly()->default(0),
                        
                        TextInput::make('total_quantite')
                            ->label('Quantité Totale Reçue (unités)')
                            ->numeric()->readOnly()->default(0),

                        Textarea::make('notes')->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        // On s'assure de lire les données du repeater avec le bon nom ('items').
        $details = $get('items');
        $totalQuantite = 0;
        $montantTotal = 0;

        if (is_array($details)) {
            foreach ($details as $item) {
                $quantite = $item['quantite'] ?? 0;
                $prixAchat = $item['prix_achat_unitaire'] ?? 0;
                
                $totalQuantite += $quantite;
                $montantTotal += $quantite * $prixAchat;
            }
        }
        $set('total_quantite', $totalQuantite);
        $set('montant_total_arrivage', $montantTotal);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fournisseur.nom_entreprise')->searchable()->sortable()->label('Fournisseur'),
                TextColumn::make('numero_bon_livraison')->searchable()->label('N° Bon Livraison'),
                TextColumn::make('date_arrivage')->date('d/m/Y')->sortable()->label('Date'),
                TextColumn::make('montant_total_arrivage')->label('Coût Total')->money('XOF')->sortable(),
                TextColumn::make('total_quantite')->label('Qté Totale')->sortable(),
                TextColumn::make('user.name')->label('Enregistré par')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date_arrivage', 'desc')
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
        return [];
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