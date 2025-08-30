<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VenteDirecteResource\Pages;
use App\Models\Order;
use App\Models\UniteDeVente;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VenteDirecteResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $slug = 'ventes-directes';
    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $navigationGroup = 'Ventes & Commandes';
    protected static ?int $navigationSort = 3;
    protected static ?string $label = 'Vente Directe';
    protected static ?string $pluralLabel = 'Ventes Directes';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_vente_directe', true);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Client & Vente')
                    ->schema([
                        Select::make('client_id')
                            ->relationship('client', 'nom', fn (Builder $query) => $query->whereNotNull('nom'))
                            ->searchable()->preload()->label('Client (Facultatif)'),
                        DatePicker::make('date_vente')->label('Date de la vente')->default(now())->required(),
                    ])->columns(2),
                Section::make('Articles Vendus')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('unite_de_vente_id')
                                    ->relationship('uniteDeVente', 'nom_unite', fn (Builder $query) => $query->whereNotNull('nom_unite'))
                                    ->searchable()->preload()->required()->live()
                                    ->afterStateUpdated(fn (Set $set, $state) => $set('prix_unitaire', UniteDeVente::find($state)?->prix_unitaire ?? 0))
                                    ->label('Produit'),
                                TextInput::make('quantite')->numeric()->required()->live(onBlur: true)->default(1),
                                TextInput::make('prix_unitaire')->numeric()->required()->live(onBlur: true),
                                Placeholder::make('prix_total_ligne')->label('Total Ligne')
                                    ->content(fn (Get $get): string => number_format(($get('quantite') ?? 0) * ($get('prix_unitaire') ?? 0), 0, ',', ' ') . ' FCFA'),
                            ])
                            ->columns(4)->addActionLabel('Ajouter un article')->collapsible()->reorderable(false)->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateTotals($get, $set))
                            ->deleteAction(fn (FormAction $action) => $action->after(fn (Get $get, Set $set) => self::updateTotals($get, $set))),
                    ]),
                Section::make('Paiement & Résumé')
                    ->schema([
                        Select::make('methode_paiement')->options(['especes' => 'Espèces', 'cheque' => 'Chèque', 'virement' => 'Virement', 'mobile' => 'Mobile Money'])
                            ->required()->label('Méthode de Paiement'),
                        TextInput::make('montant_total')->numeric()->readOnly()->prefix('FCFA')->label('Montant Total à Payer'),
                        Textarea::make('notes')->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_commande')->searchable()->sortable(),
                TextColumn::make('client.nom')->searchable()->sortable()->default('Client de Passage'),
                BadgeColumn::make('statut')->colors(['success' => 'livree', 'danger' => 'annulee']),
                BadgeColumn::make('statut_paiement')->colors(['success' => 'Complètement réglé', 'danger' => 'Annulé']),
                TextColumn::make('montant_total')->money('XOF')->sortable(),
                TextColumn::make('created_at')->dateTime('d/m/Y')->label('Date')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                // NOUVEAU BOUTON : Pour annuler la vente.
                TableAction::make('annuler')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Annuler cette vente ?')
                    ->modalDescription('Cette action remettra les produits dans le stock principal. Cette opération est irréversible.')
                    ->action(function (Order $record) {
                        $record->annulerVente();
                    })
                    // Le bouton disparaît si la vente est déjà annulée.
                    ->hidden(fn (Order $record): bool => $record->statut === 'annulee'),
            ]);
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $total = collect($get('items'))->sum(fn($item) => ($item['quantite'] ?? 0) * ($item['prix_unitaire'] ?? 0));
        $set('montant_total', $total);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVenteDirectes::route('/'),
            'create' => Pages\CreateVenteDirecte::route('/create'),
            'view' => Pages\ViewVenteDirecte::route('/{record}'),
        ];
    }
}