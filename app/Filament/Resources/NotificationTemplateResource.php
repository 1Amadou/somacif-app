<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationTemplateResource\Pages;
use App\Models\NotificationTemplate;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NotificationTemplateResource extends Resource
{
    protected static ?string $model = NotificationTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $label = 'Modèle de Notification';
    protected static ?string $pluralLabel = 'Modèles de Notification';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Configuration du Modèle')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom du modèle (interne)')
                            ->helperText('Un nom pour identifier facilement ce modèle, ex: "Nouvelle Commande Client".')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->options([
                                'email' => 'Email',
                                'sms' => 'SMS',
                            ])
                            ->required()
                            ->live(), // Indispensable pour les champs conditionnels
                    ])->columns(2),

                Section::make('Contenu du Message')
                    ->schema([
                        // CHAMP D'AIDE : Explique les variables disponibles.
                        Placeholder::make('variables_aide')
                            ->label('Variables Disponibles')
                            ->content('Utilisez les variables suivantes dans votre sujet et votre corps de message. Elles seront remplacées par les vraies valeurs lors de l\'envoi.')
                            ->columnSpanFull(),
                        
                        Placeholder::make('variables_list')
                            ->label('Exemples de variables')
                            ->content(fn (Get $get): string => match ($get('type')) {
                                'email' => '`{client_name}`, `{order_number}`, `{order_total}`, `{order_date}`',
                                'sms' => '`{client_name}`, `{order_number}`. Soyez concis pour les SMS.',
                                default => 'Aucune variable pour ce type.',
                            })
                            ->columnSpanFull(),

                        // CHAMP CONDITIONNEL : N'apparaît que pour les emails.
                        TextInput::make('subject')
                            ->label('Sujet de l\'email')
                            ->required()
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => $get('type') === 'email'),

                        // CHAMP CONDITIONNEL : Éditeur riche pour email, simple pour SMS.
                        RichEditor::make('body')
                            ->label('Corps du message')
                            ->required()
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => $get('type') === 'email'),
                        
                        Textarea::make('body')
                            ->label('Corps du message')
                            ->required()
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => $get('type') === 'sms'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom du modèle')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => 'email',
                        'success' => 'sms',
                    ]),
                TextColumn::make('subject')
                    ->label('Sujet')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationTemplates::route('/'),
            'create' => Pages\CreateNotificationTemplate::route('/create'),
            'edit' => Pages\EditNotificationTemplate::route('/{record}/edit'),
        ];
    }    
}