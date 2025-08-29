<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReglementResource\Pages;
use App\Models\Client;
use App\Models\Reglement;
use App\Models\UniteDeVente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ReglementResource extends Resource
{
    protected static ?string $model = Reglement::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Ventes';
    protected static ?string $navigationLabel = 'Règlements Clients';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Client et Commandes')
                        ->schema([
                            Forms\Components\Select::make('client_id')
                                ->relationship('client', 'nom')
                                ->label('Client / Distributeur')
                                ->live()
                                ->required()
                                ->afterStateUpdated(fn (Set $set) => $set('orders', [])),
                            Forms\Components\CheckboxList::make('orders')
                                ->label('Commandes à Régler')
                                ->helperText('Sélectionnez les commandes concernées par ce règlement.')
                                ->options(function (Get $get): array {
                                    $client = Client::find($get('client_id'));
                                    if (!$client) {
                                        return [];
                                    }
                                    return $client->orders()
                                        ->where('statut', 'Validée')
                                        ->pluck('numero_commande', 'id')
                                        ->toArray();
                                })
                                ->live()
                                ->columns(3),
                        ]),
                    Forms\Components\Wizard\Step::make('Détail des Ventes')
                        ->schema([
                            Forms\Components\Repeater::make('details')
                                ->relationship()
                                ->label('Ventes déclarées par le client')
                                ->helperText('Remplissez les quantités vendues et les prix de vente réels.')
                                ->schema([
                                    Forms\Components\Select::make('unite_de_vente_id')
                                        ->label('Produit Vendu')
                                        ->options(function (Get $get): array {
                                            $orderIds = $get('../../orders');
                                            if (empty($orderIds)) return [];
                                            return UniteDeVente::whereHas('orderItems', fn ($q) => $q->whereIn('order_id', $orderIds))
                                                ->get()
                                                ->mapWithKeys(fn ($u) => [$u->id => $u->nom_unite . ' (' . $u->calibre . ')'])
                                                ->toArray();
                                        })
                                        ->required(),
                                    Forms\Components\TextInput::make('quantite_vendue')
                                        ->label('Quantité Vendue')->numeric()->required()->live(onBlur: true),
                                    Forms\Components\TextInput::make('prix_de_vente_unitaire')
                                        ->label('Prix de vente')->numeric()->required()->live(onBlur: true),
                                ])
                                ->addActionLabel('Ajouter une ligne de vente')
                                ->columns(3)->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    self::updateTotals($get, $set);
                                }),
                        ]),
                    Forms\Components\Wizard\Step::make('Versement et Validation')
                        ->schema([
                            // CORRECTION : On regroupe tous les champs du règlement ici
                            Forms\Components\DatePicker::make('date_reglement')
                                ->label('Date du règlement')->required()->default(now()),
                            Forms\Components\TextInput::make('montant_verse')
                                ->label('Montant Versé par le client')->required()->numeric()->prefix('CFA'),
                            Forms\Components\Select::make('methode_paiement')
                                ->options(['Espèces' => 'Espèces', 'Chèque' => 'Chèque', 'Virement' => 'Virement'])
                                ->default('Espèces'),
                            Forms\Components\TextInput::make('montant_calcule')
                                ->label('Total des ventes déclarées')->numeric()->readOnly()->prefix('CFA'),
                            Forms\Components\Textarea::make('notes')->columnSpanFull(),
                        ])->columns(2),
                ])->columnSpanFull(),
                Forms\Components\Hidden::make('user_id')->default(Auth::id())
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date_reglement')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('client.nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('montant_verse')->money('cfa')->sortable()->label('Montant Versé'),
                Tables\Columns\TextColumn::make('montant_calcule')->money('cfa')->sortable()->label('Montant Calculé'),
                Tables\Columns\TextColumn::make('user.name')->label('Enregistré par'),
            ])
            ->defaultSort('date_reglement', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('pdf')
                    ->label('Imprimer Bordereau')
                    ->icon('heroicon-o-printer')
                    ->url(fn (Reglement $record): string => route('reglement.pdf', $record))
                    ->openUrlInNewTab(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReglements::route('/'),
            'create' => Pages\CreateReglement::route('/create'),
            'edit' => Pages\EditReglement::route('/{record}/edit'),
        ];
    }
    
    public static function updateTotals(Get $get, Set $set): void
    {
        $total = 0;
        $details = $get('details');

        if (is_array($details)) {
            foreach ($details as $item) {
                if (!empty($item['quantite_vendue']) && !empty($item['prix_de_vente_unitaire'])) {
                    $total += $item['quantite_vendue'] * $item['prix_de_vente_unitaire'];
                }
            }
        }
        $set('montant_calcule', $total);
    }
}