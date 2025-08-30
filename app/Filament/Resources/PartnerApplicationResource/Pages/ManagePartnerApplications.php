<?php

namespace App\Filament\Resources\PartnerApplicationResource\Pages;

use App\Filament\Resources\PartnerApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePartnerApplications extends ManageRecords
{
    protected static string $resource = PartnerApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
