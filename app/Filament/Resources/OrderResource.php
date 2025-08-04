<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Commandes';
    protected static ?int $navigationSort = 0; // Pour le mettre en haut de la liste

    public static function canCreate(): bool
    {
        return false; // On ne crée pas de commandes depuis l'admin
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Le formulaire ne sert qu'à modifier le statut
                Forms\Components\Select::make('statut')
                    ->options([
                        'Reçue' => 'Reçue',
                        'Validée' => 'Validée',
                        'En préparation' => 'En préparation',
                        'Expédiée' => 'Expédiée',
                        'Livrée' => 'Livrée',
                        'Annulée' => 'Annulée',
                    ])->required(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_commande')->searchable(),
                Tables\Columns\TextColumn::make('client.nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('montant_total')->money('XOF'),
                Tables\Columns\SelectColumn::make('statut')
                    ->options([
                        'Reçue' => 'Reçue',
                        'Validée' => 'Validée',
                        'En préparation' => 'En préparation',
                        'Expédiée' => 'Expédiée',
                        'Livrée' => 'Livrée',
                        'Annulée' => 'Annulée',
                    ]),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'Reçue' => 'Reçue',
                        'Validée' => 'Validée',
                        'En préparation' => 'En préparation',
                        'Expédiée' => 'Expédiée',
                        'Livrée' => 'Livrée',
                        'Annulée' => 'Annulée',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // On peut ajouter une ViewAction plus tard pour voir le détail complet
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}