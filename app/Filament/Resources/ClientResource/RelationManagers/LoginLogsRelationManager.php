<?php
namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LoginLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'loginLogs';
    protected static ?string $title = 'Historique des Connexions';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ip_address')
            ->columns([
                Tables\Columns\TextColumn::make('ip_address')->label('Adresse IP'),
                Tables\Columns\TextColumn::make('user_agent')->label('Appareil')->limit(50),
                Tables\Columns\TextColumn::make('login_at')->label('Date')->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}