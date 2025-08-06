<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AllInvoicesWidget;
use App\Filament\Widgets\FinancialStatsOverview;
use Filament\Pages\Page;

class Invoices extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string $view = 'filament.pages.invoices';
    protected static ?string $navigationLabel = 'Facturation';
    protected static ?int $navigationSort = -1;

    public function getTitle(): string
    {
        return 'Gestion de la Facturation';
    }

    public function getHeaderWidgets(): array
    {
        return [
            FinancialStatsOverview::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            AllInvoicesWidget::class,
        ];
    }

    
    public function getWidgetColumns(): int | array
    {
        return 1;
    }
}