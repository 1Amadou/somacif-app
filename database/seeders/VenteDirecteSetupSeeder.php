<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\PointDeVente;
use Illuminate\Database\Seeder;

class VenteDirecteSetupSeeder extends Seeder
{
    public function run(): void
    {
        // Crée un client générique pour les ventes au comptoir
        $clientComptoir = Client::firstOrCreate(
            ['email' => 'comptoir@somacif.net'],
            [
                'nom' => 'Client Comptoir SOMACIF',
                'type' => 'Détaillant',
                'telephone' => '00000000',
                'password' => bcrypt('password') 
            ]
        );

        // Crée le point de vente qui représente notre entrepôt
        PointDeVente::firstOrCreate(
            ['nom' => 'Entrepôt Principal (Ventes Directes)'],
            ['responsable_id' => $clientComptoir->id]
        );
    }
}