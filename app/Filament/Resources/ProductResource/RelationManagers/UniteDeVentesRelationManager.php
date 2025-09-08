<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\UniteDeVente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class UniteDeVentesRelationManager extends RelationManager
{
    protected static string $relationship = 'uniteDeVentes';
    protected static ?string $title = 'Unités de Vente (Calibres, Conditionnements)';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom_unite')
                    ->label('Nom de l\'unité (ex: Carton, Sachet)')
                    ->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => $this->updateNomComplet($set, $get)),

                Forms\Components\TextInput::make('calibre')
                    ->label('Calibre (ex: Moyen, 100-200g)')
                    ->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, Get $get) => $this->updateNomComplet($set, $get)),

                Forms\Components\TextInput::make('nom_complet')
                    ->label('Nom Complet (auto-généré)')
                    ->required()
                    ->unique(table: 'unite_de_ventes', column: 'nom_complet', ignoreRecord: true)
                    ->disabled()
                    ->dehydrated(),
                
                // Le champ de stock a été supprimé car il n'existe plus directement ici.

                Forms\Components\Fieldset::make('Grille Tarifaire (FCFA)')
                    ->schema([
                        Forms\Components\TextInput::make('prix_particulier')
                            ->label('Prix Particulier')
                            ->required()->numeric()->minValue(0),
                        Forms\Components\TextInput::make('prix_grossiste')
                            ->label('Prix Grossiste')
                            ->numeric()->minValue(0)->nullable(),
                        Forms\Components\TextInput::make('prix_hotel_restaurant')
                            ->label('Prix Hôtel/Restaurant')
                            ->numeric()->minValue(0)->nullable(),
                    ])->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nom_complet')
            ->columns([
                Tables\Columns\TextColumn::make('nom_complet')->searchable()->sortable(),
                
                // *** MIS À JOUR POUR LA NOUVELLE LOGIQUE DE STOCK ***
                Tables\Columns\TextColumn::make('stock_entrepôt_principal')
                    ->label('Stock Principal')
                    ->badge()->color('primary'),
                
                Tables\Columns\TextColumn::make('prix_particulier')
                    ->label('Prix Particulier')->money('XOF'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $productName = $this->getOwnerRecord()->nom;
                        $data['nom_complet'] = "{$productName} ({$data['nom_unite']} - {$data['calibre']})";
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make()
                    ->before(function (UniteDeVente $record, DeleteAction $action) {
                        if ($this->isDeletionForbidden($record)) {
                            Notification::make()
                                ->title('Suppression impossible')
                                ->body('Cette U.V. a du stock ou est utilisée dans des transactions.')
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (Collection $records) {
                            $forbidden = $records->some(fn($record) => $this->isDeletionForbidden($record));

                            if ($forbidden) {
                                Notification::make()
                                    ->title('Suppression impossible')
                                    ->body('Au moins une des U.V. sélectionnées a du stock ou est utilisée.')
                                    ->danger()
                                    ->send();
                            } else {
                                $records->each->delete();
                                Notification::make()->title('Unités de vente supprimées.')->success()->send();
                            }
                        }),
                ]),
            ]);
    }

    private function updateNomComplet(Set $set, Get $get): void
    {
        $productName = $this->getOwnerRecord()->nom;
        $nomUnite = $get('nom_unite') ?? '';
        $calibre = $get('calibre') ?? '';
        $set('nom_complet', "{$productName} ({$nomUnite} - {$calibre})");
    }

    // *** MIS À JOUR POUR LA NOUVELLE LOGIQUE DE STOCK ***
    private function isDeletionForbidden(UniteDeVente $record): bool
    {
        return $record->stock_entrepôt_principal > 0 // On utilise le nouvel accesseur
            || $record->inventories()->where('quantite_stock', '>', 0)->exists() // Vérifie aussi les autres stocks
            || $record->arrivageItems()->exists()
            || $record->orderItems()->exists()
            || $record->venteDirecteItems()->exists();
    }
}