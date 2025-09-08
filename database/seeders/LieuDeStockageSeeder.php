<?php

namespace Database\Seeders;

use App\Models\LieuDeStockage;
use Illuminate\Database\Seeder;

class LieuDeStockageSeeder extends Seeder
{
    public function run(): void
    {
        // Crée l'entrepôt principal s'il n'existe pas déjà.
        LieuDeStockage::firstOrCreate(
            ['nom' => 'Entrepôt Principal'],
            ['type' => 'entrepot']
        );
    }
}