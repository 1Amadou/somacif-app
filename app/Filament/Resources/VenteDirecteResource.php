<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VenteDirecteResource\Pages;
use App\Models\Client;
use App\Models\UniteDeVente;
use App\Models\VenteDirecte;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VenteDirecteResource extends Resource
{
    protected static ?string $model = VenteDirecte::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Ventes en Gros';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = 'Vente Directe';
    protected static ?string $pluralLabel = 'Ventes Directes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations sur la Vente')
                    ->schema([
                        TextInput::make('numero_facture')
                            ->default('FD-' . random_int(100000, 999999))
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Select::make('client_id')
                            ->relationship('client', 'nom', fn (Builder $query) => $query->whereNotNull('nom'))
                            ->searchable()->preload()->required()
                            ->label('Client'),
                        DatePicker::make('date_vente')
                            ->default(now())
                            ->required()
                            ->label('Date de la vente'),
                    ])->columns(3),

                Section::make('Articles Vendus')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('unite_de_vente_id')
                                    ->relationship('uniteDeVente', 'nom_unite')
                                    ->searchable()->preload()->required()->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $unite = UniteDeVente::find($state);
                                        $set('prix_unitaire', $unite?->prix_unitaire ?? 0);
                                    })
                                    ->label('Produit (Unité de vente)'),
                                TextInput::make('quantite')
                                    ->numeric()->required()->live(onBlur: true)->default(1),
                                TextInput::make('prix_unitaire')
                                    ->numeric()->required()->live(onBlur: true),
                                Placeholder::make('total_ligne')
                                    ->label('Total Ligne')
                                    ->content(fn (Get $get): string => number_format(($get('quantite') ?? 0) * ($get('prix_unitaire') ?? 0), 0, ',', ' ') . ' FCFA'),
                            ])
                            ->columns(4)
                            ->addActionLabel('Ajouter un article')
                            ->collapsible()->reorderable(false)->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateGrandTotal($get, $set))
                            ->deleteAction(fn (Forms\Components\Actions\Action $action) => $action->after(fn (Get $get, Set $set) => self::updateGrandTotal($get, $set))),
                    ]),

                Section::make('Résumé Financier et Notes')
                    ->schema([
                        TextInput::make('montant_total')
                            ->numeric()->readOnly()->prefix('FCFA')
                            ->label('Montant Total de la Vente'),
                        Textarea::make('notes')->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_facture')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('client.nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('date_vente')->date('d/m/Y')->label('Date')->sortable(),
                Tables\Columns\TextColumn::make('montant_total')->money('XOF')->sortable(),
            ])
            ->defaultSort('date_vente', 'desc')
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

    public static function updateGrandTotal(Get $get, Set $set): void
    {
        $total = collect($get('items'))->sum(function (array $item) {
            return ($item['quantite'] ?? 0) * ($item['prix_unitaire'] ?? 0);
        });
        $set('montant_total', $total);
    }
    
    public static function getRelations(): array
    {
        return [];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVenteDirectes::route('/'),
            'create' => Pages\CreateVenteDirecte::route('/create'),
            'edit' => Pages\EditVenteDirecte::route('/{record}/edit'),
            'view' => Pages\ViewVenteDirecte::route('/{record}'),
        ];
    }
}