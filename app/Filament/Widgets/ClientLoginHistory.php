<?php

namespace App\Filament\Widgets;

use App\Models\ClientLoginLog; // Ligne ajoutée pour importer le modèle
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ClientLoginHistory extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(ClientLoginLog::latest('login_at'))
            ->heading('Historique des Connexions Partenaires')
            ->columns([
                Tables\Columns\TextColumn::make('client.nom')->label('Partenaire')->searchable(),
                Tables\Columns\TextColumn::make('ip_address')->label('Adresse IP'),
                Tables\Columns\TextColumn::make('login_at')->label('Date de connexion')->dateTime('d/m/Y à H:i'),
            ]);
    }
}