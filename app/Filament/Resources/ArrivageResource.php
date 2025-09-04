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
use App\Filament\Resources\PointDeVenteResource;

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
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Fournisseur'),
                        
                        TextInput::make('numero_bon_livraison')
                            ->required()
                            ->unique(ignoreRecord: true) // Ajout de l'unicité
                            ->maxLength(255)
                            ->label('Numéro du Bon de Livraison'),

                        DatePicker::make('date_arrivage')
                            ->required()
                            ->default(now())
                            ->label('Date de l\'arrivage'),
                    ])->columns(3), // On passe à 3 colonnes pour une meilleure lisibilité

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
                                    ->exists('unite_de_ventes', 'id')
                                    ->live(onBlur: true),
                                
                                TextInput::make('quantite')
                                    ->label('Quantité (en unités)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->live(onBlur: true),
                                
                                TextInput::make('prix_achat_unitaire') // <-- NOUVEAU CHAMP
                                    ->label('Prix d\'Achat Unitaire')
                                    ->prefix('FCFA')
                                    ->numeric()
                                    ->required()
                                    ->live(onBlur: true), // 'live' pour les calculs en temps réel
                            ])
                            ->columns(3) // Changement ici aussi
                            ->addActionLabel('Ajouter un produit')
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateTotals($get, $set);
                            })
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action->after(fn (Get $get, Set $set) => self::updateTotals($get, $set)),
                            )
                            ->reorderable(true) // Rendre le repeater ordonnable
                            ->collapsible(),
                    ]),
                
                Section::make('Résumé et Notes')
                    ->schema([
                        TextInput::make('montant_total_arrivage') // <-- NOUVEAU CHAMP
                            ->label('Montant Total de l\'Arrivage')
                            ->prefix('FCFA')
                            ->numeric()
                            ->readOnly()
                            ->default(0)
                            ->dehydrated(true), // Assurez-vous que la valeur est sauvegardée
                        
                        TextInput::make('total_quantite') // Ancien champ renommé pour plus de clarté
                            ->label('Quantité Totale Reçue')
                            ->numeric()
                            ->readOnly()
                            ->default(0),

                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $details = $get('details_produits');
        $totalQuantite = 0;
        $montantTotal = 0; // <-- NOUVEAU

        if (is_array($details)) {
            foreach ($details as $item) {
                $quantite = $item['quantite'] ?? 0;
                $prixAchat = $item['prix_achat_unitaire'] ?? 0;
                
                $totalQuantite += $quantite;
                $montantTotal += $quantite * $prixAchat; // Calcul du montant total
            }
        }
        $set('total_quantite', $totalQuantite);
        $set('montant_total_arrivage', $montantTotal);
    }
    
    // ... (Le reste des méthodes `table`, `getRelations`, `getPages` restent identiques)
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
                TextColumn::make('montant_total_arrivage') // <-- NOUVEAU
                    ->label('Coût Total')
                    ->money('XOF')
                    ->sortable(),
                TextColumn::make('total_quantite') // Ancien champ renommé
                    ->label('Qté Totale')
                    ->sortable()
                    ->default('N/A'),
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