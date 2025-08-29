<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';
    protected static ?string $title = 'Historique des Commandes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Le formulaire est vide car on ne crée pas de commande depuis ici
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_commande')
            ->columns([
                Tables\Columns\TextColumn::make('numero_commande'),
                Tables\Columns\TextColumn::make('statut')->badge(),
                Tables\Columns\TextColumn::make('montant_total')->money('cfa'),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}