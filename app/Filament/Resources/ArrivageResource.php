<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArrivageResource\Pages;
use App\Models\Arrivage;
use App\Models\Product;
use App\Models\UniteDeVente;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;

class ArrivageResource extends Resource
{
    protected static ?string $model = Arrivage::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationGroup = 'Stock & Catalogue';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations sur l\'arrivage')
                    ->description('Détails sur la cargaison entrante.')
                    ->schema([
                        Forms\Components\Select::make('fournisseur_id')
                            ->relationship('fournisseur', 'nom_entreprise')
                            ->label('Fournisseur')
                            ->searchable()
                            ->required()
                            ->helperText('Sélectionnez le fournisseur qui a livré la marchandise.'),
                        Forms\Components\TextInput::make('numero_bon_livraison')
                            ->label('Numéro du Bon de Livraison')
                            ->default('BL-' . strtoupper(uniqid()))
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\DateTimePicker::make('date_arrivage')
                            ->label('Date de l\'arrivage')
                            ->default(now())
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes supplémentaires')
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Détails des Produits Reçus')
                    ->description('Ajoutez ici chaque type de produit reçu dans cet arrivage. Le stock sera mis à jour automatiquement.')
                    ->schema([
                        Forms\Components\Repeater::make('details_produits')
                            ->label('Produits Reçus')
                            ->schema([
                                Forms\Components\Select::make('unite_de_vente_id')
                                    ->label('Produit (Unité / Calibre)')
                                    ->options(UniteDeVente::all()->mapWithKeys(fn ($unite) => [$unite->id => $unite->nom_complet]))
                                    ->searchable()
                                    ->required(),
                                Forms\Components\TextInput::make('quantite_cartons')
                                    ->label('Nombre de cartons')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Ajouter un produit à l\'arrivage')
                            ->required(),
                    ]),
                // NOUVELLE SECTION POUR LA CLÔTURE
                Forms\Components\Section::make('Clôture de l\'arrivage')
                    ->description('Attachez le bon de décharge signé ici pour finaliser et verrouiller l\'arrivage.')
                    ->schema([
                        FileUpload::make('decharge_signee_path')
                            ->label('Bon de décharge signé')
                            ->disk('public')
                            ->directory('decharges')
                            ->visibility('private')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->afterStateUpdated(function (Set $set) {
                                $set('statut', 'cloture'); // Change le statut automatiquement
                            })
                            ->live(), // Important pour que le statut se mette à jour en direct

                        // MESSAGE D'AVERTISSEMENT
                        Placeholder::make('message_verrouillage')
                            ->content('Une fois le bon de décharge attaché, cet arrivage sera clôturé et ne pourra plus être modifié.')
                            ->visible(fn (Arrivage $record = null) => $record && $record->statut !== 'cloture'),

                        Placeholder::make('message_deja_verrouille')
                            ->label('Statut')
                            ->content(fn (Arrivage $record = null) => $record && $record->statut === 'cloture'
                                ? 'Cet arrivage est CLÔTURÉ. Pour ajuster le stock, veuillez effectuer un transfert.'
                                : 'En cours de déchargement.'
                            ),
                    ])
                    // Cette section n'apparaît que pour les arrivages déjà créés
                    ->visibleOn('edit'),

                Forms\Components\Hidden::make('user_id')->default(Auth::id()),
            ])
            // On désactive tout le formulaire si l'arrivage est clôturé
            ->disabled(fn (Arrivage $record = null) => $record && $record->statut === 'cloture');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date_arrivage')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fournisseur.nom_entreprise')
                    ->label('Fournisseur')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('numero_bon_livraison')
                    ->label('N° Bon Livraison')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Modifier l\'arrivage')
                    ->modalDescription('Êtes-vous sûr de vouloir modifier cet arrivage ? Cette action recalculera le stock. Assurez-vous que cela correspond bien à une correction réelle.')
                    ->modalSubmitActionLabel('Oui, modifier'),

                Action::make('pdf')
                    ->label('Imprimer Décharge')
                    ->icon('heroicon-o-printer')
                    ->action(function (Arrivage $record) {
                        $pdf = Pdf::loadView('invoices.bon-decharge', ['arrivage' => $record]);
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, $record->numero_bon_livraison . '-decharge.pdf');
                    }),
            ])
            ->defaultSort('date_arrivage', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArrivages::route('/'),
            'create' => Pages\CreateArrivage::route('/create'),
            'view' => Pages\ViewArrivage::route('/{record}'),
            'edit' => Pages\EditArrivage::route('/{record}/edit'),
        ];
    }
}