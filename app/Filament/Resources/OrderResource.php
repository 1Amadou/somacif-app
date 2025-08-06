<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Commandes';
    protected static ?int $navigationSort = 0;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Détails de la Commande')->schema([
                    Infolists\Components\TextEntry::make('numero_commande'),
                    Infolists\Components\TextEntry::make('client.nom'),
                    Infolists\Components\TextEntry::make('livreur.name')->label('Livreur')->placeholder('Non assigné'),
                    Infolists\Components\TextEntry::make('statut')->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'Reçue' => 'gray', 'Validée' => 'info', 'En préparation' => 'warning',
                            'En cours de livraison' => 'primary', 'Livrée' => 'success', 'Annulée' => 'danger',
                        }),
                ])->columns(2),
                Infolists\Components\Section::make('Détails Financiers')->schema([
                    Infolists\Components\TextEntry::make('montant_total')->money('XOF'),
                    Infolists\Components\TextEntry::make('amount_paid')->label('Montant Payé')->money('XOF'),
                    Infolists\Components\TextEntry::make('remaining_balance')->label('Solde Restant')->money('XOF')
                        ->color(fn ($state) => $state > 0 ? 'warning' : 'success'),
                    Infolists\Components\TextEntry::make('due_date')->label('Échéance')->date('d/m/Y'),
                ])->columns(2),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Gestion de la Commande')->schema([
                    Forms\Components\Select::make('statut')->options([
                        'Reçue' => 'Reçue', 'Validée' => 'Validée', 'En préparation' => 'En préparation',
                        'En cours de livraison' => 'En cours de livraison', 'Livrée' => 'Livrée', 'Annulée' => 'Annulée',
                    ])->required(),
                    Forms\Components\Select::make('livreur_id')->relationship('livreur', 'name')->searchable(),
                    Forms\Components\DatePicker::make('due_date')->label('Échéance de paiement'),
                    Forms\Components\Textarea::make('notes')->columnSpanFull(),
                ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_commande')->searchable(),
                Tables\Columns\TextColumn::make('client.nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('montant_total')->money('XOF')->sortable(),
                Tables\Columns\TextColumn::make('remaining_balance')->label('Solde Restant')->money('XOF')->sortable(),
                Tables\Columns\SelectColumn::make('statut')->options([ // Modification rapide du statut
                    'Reçue' => 'Reçue', 'Validée' => 'Validée', 'En préparation' => 'En préparation',
                    'En cours de livraison' => 'En cours de livraison', 'Livrée' => 'Livrée', 'Annulée' => 'Annulée',
                ]),
                Tables\Columns\TextColumn::make('due_date')->label('Échéance')->date('d/m/Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }    
}