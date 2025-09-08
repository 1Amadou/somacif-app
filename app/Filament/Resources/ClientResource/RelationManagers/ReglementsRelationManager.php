<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Filament\Resources\ReglementResource;
use App\Models\Reglement; // <-- AJOUT pour le typage
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ReglementsRelationManager extends RelationManager
{
    protected static string $relationship = 'reglements';
    protected static ?string $title = 'Historique des Règlements';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('date_reglement')->label('Date')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('montant_verse')->label('Montant Versé')->money('XOF')->sortable(),
                Tables\Columns\TextColumn::make('montant_calcule')->label('Ventes Déclarées')->money('XOF')->sortable(),
                Tables\Columns\TextColumn::make('methode_paiement')->label('Méthode')->badge(),
            ])
            ->filters([
                Filter::make('date_reglement')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Du'),
                        Forms\Components\DatePicker::make('created_until')->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn (Builder $query, $date): Builder => $query->whereDate('date_reglement', '>=', $date))
                            ->when($data['created_until'], fn (Builder $query, $date): Builder => $query->whereDate('date_reglement', '<=', $date));
                    })
            ])
            ->headerActions([
                Tables\Actions\Action::make('create_reglement')
                    ->label('Nouveau Règlement')
                    ->url(fn () => ReglementResource::getUrl('create', ['client_id' => $this->getOwnerRecord()->id]))
                    ->icon('heroicon-o-plus-circle'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Reglement $record): string => ReglementResource::getUrl('view', ['record' => $record])),
                Tables\Actions\EditAction::make()
                    ->url(fn (Reglement $record): string => ReglementResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->url(fn () => ReglementResource::getUrl('create', ['client_id' => $this->getOwnerRecord()->id])),
            ]);
    }
}