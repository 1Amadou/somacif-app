<?php

namespace App\Filament\Resources\ReglementResource\Pages;

use App\Filament\Resources\ReglementResource;
use App\Models\Reglement;
use App\Observers\ReglementObserver;
use App\Services\StockManager;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateReglement extends CreateRecord
{
    protected static string $resource = ReglementResource::class;

    /**
     * LOGIQUE CONNECTÉE : C'est ici que le lien se fait avec notre code testé.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $reglement = null;

        DB::transaction(function () use ($data, &$reglement) {
            $ordersData = $data['orders'] ?? [];
            $detailsData = $data['details'] ?? [];
            unset($data['orders'], $data['details']);

            $reglement = static::getModel()::create($data);

            if (!empty($ordersData)) {
                $reglement->orders()->attach($ordersData);
            }
            if (!empty($detailsData)) {
                $reglement->details()->createMany($detailsData);
            }
        });

        if ($reglement) {
            // On déclenche manuellement notre logique après la sauvegarde.
            (new ReglementObserver(new StockManager()))->process($reglement);
        }

        return $reglement;
    }
}