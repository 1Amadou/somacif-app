<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::firstOrCreate(
            ['identifiant_unique_somacif' => 'CLI-HOTEL-AZALAI'],
            [
                'nom' => 'Hôtel Azalaï Bamako',
                'type' => 'Hôtel/Restaurant',
                'telephone' => '20221111',
                'email' => 'azalai@example.com',
                'entrepots_de_livraison' => json_encode(['Dépôt principal ACI 2000', 'Entrepôt secondaire de Sotuba']),
            ]
        );

        Client::firstOrCreate(
            ['identifiant_unique_somacif' => 'CLI-GROS-DIARRA'],
            [
                'nom' => 'Grossiste Amadou Diarra',
                'type' => 'Grossiste',
                'telephone' => '76102030',
                'email' => 'diarra.gros@example.com',
                'entrepots_de_livraison' => ['Dépôt principal ACI 2000', 'Entrepôt secondaire de Sotuba'],
            ]
        );
    }
}