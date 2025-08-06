<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model; // Assurez-vous que cette ligne est présente

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $recordTitleAttribute = 'amount';
    protected static ?string $title = 'Historique des Paiements';
    
    // On ajoute cette fonction pour mettre à jour le total de la commande après chaque action
    protected function afterAction(): void
    {
        $this->getOwnerRecord()->update([
            'amount_paid' => $this->getOwnerRecord()->payments()->sum('amount'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->label('Montant Payé')
                    ->required()->numeric()->prefix('FCFA'),
                Forms\Components\DatePicker::make('payment_date')
                    ->label('Date du paiement')
                    ->default(now())->required(),
                Forms\Components\Select::make('method')
                    ->label('Méthode de paiement')
                    ->options(['Espèces' => 'Espèces', 'Virement' => 'Virement', 'Chèque' => 'Chèque', 'Autre' => 'Autre'])
                    ->default('Manuel'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')->label('Date')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('amount')->label('Montant')->money('XOF')->sortable(),
                Tables\Columns\TextColumn::make('method')->label('Méthode'),
                Tables\Columns\TextColumn::make('notes')->label('Notes')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                // Le bouton de création est bien ici
                Tables\Actions\CreateAction::make()->label('Enregistrer un nouveau paiement')
                    ->after(fn () => $this->afterAction()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(fn () => $this->afterAction()),
                Tables\Actions\DeleteAction::make()
                    ->after(fn () => $this->afterAction()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(fn () => $this->afterAction()),
                ]),
            ]);
    }
}