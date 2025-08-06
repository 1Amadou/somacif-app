<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Client;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;

class AllInvoicesWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Toutes les Factures / Commandes';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query())
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('numero_commande')->searchable(),
                Tables\Columns\TextColumn::make('client.nom')->searchable(),
                Tables\Columns\TextColumn::make('montant_total')->money('XOF'),
                Tables\Columns\TextColumn::make('remaining_balance')->label('Solde Restant')->money('XOF'),
                Tables\Columns\TextColumn::make('statut')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client')
                    ->relationship('client', 'nom')
                    ->searchable(),
                Tables\Filters\Filter::make('payment_status')
                    ->label('Statut du paiement')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                'paid' => 'Payée',
                                'unpaid' => 'Non Payée',
                                'partial' => 'Partiellement Payée',
                            ])
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['status'] === 'paid', fn (Builder $query) => $query->whereRaw('amount_paid >= montant_total'))
                            ->when($data['status'] === 'unpaid', fn (Builder $query) => $query->where('amount_paid', '=', 0))
                            ->when($data['status'] === 'partial', fn (Builder $query) => $query->whereRaw('amount_paid > 0 and amount_paid < montant_total'));
                    }),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Date (début)'),
                        Forms\Components\DatePicker::make('created_until')->label('Date (fin)'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('addPayment')
                    ->label('Ajouter un paiement')
                    ->icon('heroicon-o-currency-dollar')
                    ->form([
                        Forms\Components\TextInput::make('amount')->label('Montant Payé')->numeric()->required(),
                        Forms\Components\DatePicker::make('payment_date')->label('Date')->default(now())->required(),
                        Forms\Components\Textarea::make('notes'),
                    ])
                    ->action(function (Order $record, array $data) {
                        $record->payments()->create($data);
                        $record->update(['amount_paid' => $record->payments()->sum('amount')]);
                    }),
                Tables\Actions\Action::make('viewOrder')
                    ->label('Voir la commande')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record])),
            ]);
    }
}