<?php

namespace Database\Seeders;

use App\Models\PointDeVente;
use Illuminate\Database\Seeder;

class PointDeVenteSeeder extends Seeder
{
    public function run(): void
    {
        // On utilise la factory pour créer un point de vente.
        // La logique que nous avons ajoutée dans le modèle PointDeVente
        // va automatiquement créer le LieuDeStockage associé.
        PointDeVente::factory()->count(5)->create();
    }
}