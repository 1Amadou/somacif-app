<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Documentation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static string $view = 'filament.pages.documentation';
    protected static ?string $navigationLabel = 'Guide d\'Utilisation';
    protected static ?int $navigationSort = 100; // Pour la mettre à la fin
}