<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter; // AJOUT : Pour le filtre par date
use Illuminate\Database\Eloquent\Builder;

class ReglementsRelationManager extends RelationManager
{
    protected static string $relationship = 'reglements';
    protected static ?string $title = 'Historique des Règlements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')->required()->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Date du Versement')->dateTime('d/m/Y H:i')->sortable(),
                
                // AJOUT : Colonne pour le nombre de cartons
                Tables\Columns\TextColumn::make('details_sum_quantite_vendue')
                    ->sum('details', 'quantite_vendue')
                    ->label('Nb. Cartons Vendus')
                    ->sortable(),

                Tables\Columns\TextColumn::make('montant_verse')->label('Montant Versé')->money('XOF')->sortable(),
                Tables\Columns\TextColumn::make('methode_paiement')->label('Méthode')->badge(),
                Tables\Columns\TextColumn::make('user.name')->label('Enregistré par'),
            ])
            ->filters([
                // AJOUT : Filtre par date
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Du'),
                        Forms\Components\DatePicker::make('created_until')->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->headerActions([
                // L'action de création reste sur la page principale de la commande (ViewOrder)
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->url(fn ($record) => \App\Filament\Resources\ReglementResource::getUrl('view', ['record' => $record])),
                Tables\Actions\Action::make('print_receipt')
                    ->label('Imprimer le Reçu')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn ($record) => route('invoices.reglement-receipt', $record))
                    ->openUrlInNewTab(),
                        ])
                        ->bulkActions([]);
    }
}