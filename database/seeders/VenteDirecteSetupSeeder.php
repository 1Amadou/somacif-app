<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\PointDeVente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str; // <-- Don't forget to import the Str facade

class VenteDirecteSetupSeeder extends Seeder
{
    public function run(): void
    {
        // Crée un client générique pour les ventes au comptoir
        $clientComptoir = Client::firstOrCreate(
            ['email' => 'comptoir@somacif.net'],
            [
                'nom' => 'Client Comptoir SOMACIF',
                'type' => 'Defaut',
                'telephone' => '00000000',
                'password' => bcrypt('password'),
                'identifiant_unique_somacif' => 'COMPTOIR-VTE-DIRECTE'
            ]
        );

        // Crée le point de vente qui représente notre entrepôt
        PointDeVente::firstOrCreate(
            ['nom' => 'Entrepôt Principal (Ventes Directes)'],
            [
                'responsable_id' => $clientComptoir->id,
                'adresse' => 'CLient passant'
            ]
        );
    }
}
