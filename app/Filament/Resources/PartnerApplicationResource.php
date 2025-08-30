<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerApplicationResource\Pages;
use App\Models\Client;
use App\Models\PartnerApplication;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource; // <-- CORRECTION : L'import manquant est ici
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PartnerApplicationResource extends Resource
{
    protected static ?string $model = PartnerApplication::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'Clients & Partenaires';
    protected static ?string $label = 'Demande de Partenariat';
    protected static ?string $pluralLabel = 'Demandes de Partenariat';

    public static function form(Form $form): Form
    {
        // Le formulaire est en lecture seule car on ne modifie pas une demande, on la traite.
        return $form
            ->schema([
                TextInput::make('nom_entreprise')->disabled(),
                TextInput::make('nom_contact')->disabled(),
                TextInput::make('telephone')->disabled(),
                TextInput::make('email')->disabled(),
                TextInput::make('secteur_activite')->disabled(),
                Textarea::make('message')->columnSpanFull()->disabled(),
                Select::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'approved' => 'Approuvée',
                        'rejected' => 'Rejetée',
                    ])->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nom_entreprise')->label('Entreprise')->searchable(),
                TextColumn::make('nom_contact')->label('Contact')->searchable(),
                TextColumn::make('telephone'),
                TextColumn::make('email'),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('created_at')->label('Date de la demande')->dateTime('d/m/Y'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Action pour approuver une demande
                Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (PartnerApplication $record) {
                        // Créer un nouveau client à partir de la demande
                        $client = Client::create([
                            'nom' => $record->nom_entreprise,
                            'email' => $record->email,
                            'telephone' => $record->telephone,
                            'type' => $record->secteur_activite,
                            'status' => 'actif',
                            'password' => bcrypt(Str::random(12)), // Génère un mot de passe aléatoire
                            'identifiant_unique_somacif' => 'SOM-' . strtoupper(Str::random(8)),
                        ]);
                        
                        // Mettre à jour le statut de la demande
                        $record->update(['status' => 'approved']);

                        Notification::make()
                            ->title('Partenaire Approuvé !')
                            ->body("Le client {$client->nom} a été créé avec succès.")
                            ->success()->send();
                    })
                    // N'afficher le bouton que si la demande est en attente
                    ->visible(fn (PartnerApplication $record): bool => $record->status === 'pending'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartnerApplications::route('/'),
        ];
    }

    // On ne peut pas créer de demande depuis le back-office, elles viennent du site.
    public static function canCreate(): bool
    {
        return false;
    }
}