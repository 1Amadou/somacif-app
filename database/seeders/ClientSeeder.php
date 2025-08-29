<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Création d'un client de type Grossiste
        Client::create([
            'identifiant_unique_somacif' => 'CLI-GROS-DIARRA',
            'nom' => 'Grossiste Amadou Diarra',
            'type' => 'Grossiste',
            'telephone' => '76102030',
            'email' => 'diarra.gros@example.com',
            // CORRECTION : On transforme le tableau en chaîne JSON
            'entrepots_de_livraison' => json_encode(['Dakar - Port', 'Bamako - Sogoniko']),
            'password' => Hash::make('password'),
        ]);

        // Création d'un client de type Hôtel/Restaurant
        Client::create([
            'identifiant_unique_somacif' => 'CLI-HOTEL-AZALAI',
            'nom' => 'Hôtel Azalaï Bamako',
            'type' => 'Hôtel/Restaurant',
            'telephone' => '20221111',
            'email' => 'azalai@example.com',
            'entrepots_de_livraison' => json_encode(['Bamako - ACI 2000']),
            'password' => Hash::make('password'),
        ]);

        // Création d'un client de type Particulier
        Client::create([
            'identifiant_unique_somacif' => 'CLI-PART-COULIBALY',
            'nom' => 'Moussa Coulibaly',
            'type' => 'Particulier',
            'telephone' => '66708090',
            'email' => 'moussa.part@example.com',
            'entrepots_de_livraison' => json_encode(['Bamako Particulier - ACI 2000']),
            'password' => Hash::make('password'),
        ]);
    }
}