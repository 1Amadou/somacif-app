<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerApplicationResource\Pages;
use App\Models\Client;
use App\Models\PartnerApplication;
use App\Notifications\PartnerApprovedNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification as Notifier;
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
        return $form
            ->schema([
                Forms\Components\Section::make('Informations sur la demande')
                    ->schema([
                        Forms\Components\TextInput::make('nom_entreprise')->required()->maxLength(255)
                            // --- SÉCURITÉ : Le champ est désactivé si la demande est traitée ---
                            ->disabled(fn (?PartnerApplication $record) => $record && $record->status !== 'pending'),
                        Forms\Components\TextInput::make('nom_contact')->required()->maxLength(255)
                            ->disabled(fn (?PartnerApplication $record) => $record && $record->status !== 'pending'),
                        Forms\Components\TextInput::make('telephone')->tel()->required()
                            ->disabled(fn (?PartnerApplication $record) => $record && $record->status !== 'pending'),
                        Forms\Components\TextInput::make('email')->email()->required()
                            ->disabled(fn (?PartnerApplication $record) => $record && $record->status !== 'pending'),
                        Forms\Components\Select::make('secteur_activite')
                            ->options([
                                'Hôtel/Restaurant' => 'Hôtel/Restaurant', 'Grossiste' => 'Grossiste', 'Particulier' => 'Particulier',
                            ])->required()
                            ->disabled(fn (?PartnerApplication $record) => $record && $record->status !== 'pending'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'En attente', 'approved' => 'Approuvée', 'rejected' => 'Rejetée',
                            ])->required(),
                        Forms\Components\Textarea::make('message')->columnSpanFull()
                            ->disabled(fn (?PartnerApplication $record) => $record && $record->status !== 'pending'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom_entreprise')->label('Entreprise')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nom_contact')->label('Contact')->searchable(),
                Tables\Columns\BadgeColumn::make('status')->label('Statut')
                    ->colors(['warning' => 'pending', 'success' => 'approved', 'danger' => 'rejected'])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'En attente', 'approved' => 'Approuvée', 'rejected' => 'Rejetée', default => $state
                    })->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()
                    // --- SÉCURITÉ : Le bouton n'est visible que si la demande est en attente ---
                    ->visible(fn (PartnerApplication $record): bool => $record->status === 'pending'),
                Tables\Actions\Action::make('approve')
                    ->label('Approuver')->icon('heroicon-o-check-circle')->color('success')->requiresConfirmation()
                    ->action(function (PartnerApplication $record) {
                        // --- SÉCURITÉ : On garantit un identifiant 100% unique ---
                        do {
                            $identifiant = 'SOM-' . strtoupper(Str::random(8));
                        } while (Client::where('identifiant_unique_somacif', $identifiant)->exists());
                        
                        $password = Str::random(12);

                        $client = Client::create([
                            'nom' => $record->nom_entreprise, 'email' => $record->email,
                            'telephone' => $record->telephone, 'type' => $record->secteur_activite,
                            'status' => 'actif', 'password' => bcrypt($password),
                            'identifiant_unique_somacif' => $identifiant,
                        ]);
                        
                        $record->update(['status' => 'approved', 'client_id' => $client->id]);

                        try {
                            Notifier::route('mail', $client->email)->notify(new PartnerApprovedNotification($client, $password));
                            Notification::make()->title('Partenaire Approuvé et Notifié !')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Client créé, mais erreur d\'envoi email')->body($e->getMessage())->danger()->send();
                        }
                    })
                    ->visible(fn (PartnerApplication $record): bool => $record->status === 'pending'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }
    
    public static function getPages(): array
    {
        return ['index' => Pages\ListPartnerApplications::route('/'), 'edit' => Pages\EditPartnerApplication::route('/{record}/edit')];
    }

    public static function canCreate(): bool { return false; }

    // --- SÉCURITÉ : On interdit la modification si la demande n'est plus en attente ---
    public static function canEdit(Model $record): bool
    {
        return $record->status === 'pending';
    }
}