<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationTemplateResource\Pages;
use App\Models\NotificationTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplateResource extends Resource
{
    protected static ?string $model = NotificationTemplate::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Modèles de Notification';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->disabled()->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')->label('Activer cette notification'),
                Forms\Components\TextInput::make('subject')->label('Sujet (pour les emails)'),
                Forms\Components\Textarea::make('body')->label('Contenu du message')->rows(5)->columnSpanFull(),
                Forms\Components\Placeholder::make('description')
                    ->label('Variables disponibles')
                    ->content(fn (?Model $record): string => $record?->description ?? 'Aucune description pour ce modèle.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Nom du modèle'),
            Tables\Columns\TextColumn::make('channel')->label('Canal')->badge(),
            Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationTemplates::route('/'),
            'edit' => Pages\EditNotificationTemplate::route('/{record}/edit'),
        ];
    }    
}