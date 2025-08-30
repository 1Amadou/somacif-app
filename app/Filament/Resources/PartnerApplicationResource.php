<?php
namespace App\Filament\Resources;

// ... (imports)

class PartnerApplicationResource extends Resource
{
    protected static ?string $model = PartnerApplication::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'Clients & Partenaires';

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nom_entreprise')->searchable(),
            TextColumn::make('nom_contact')->searchable(),
            TextColumn::make('telephone'),
            TextColumn::make('email'),
            BadgeColumn::make('status')
                ->colors(['warning' => 'pending', 'success' => 'approved', 'danger' => 'rejected']),
            TextColumn::make('created_at')->dateTime('d/m/Y'),
        ])->actions([
            // Actions pour approuver ou rejeter
        ]);
    }
    
    public static function canCreate(): bool { return false; } // On ne crée pas depuis le back-office
}