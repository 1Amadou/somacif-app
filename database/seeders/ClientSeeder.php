<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\PointDeVente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création du premier client de test
        $client1 = Client::create([
            'nom' => 'Client Test Azalaï',
            'email' => 'client@azalai.com',
            'telephone' => '+22370000001',
            'type' => 'Hôtel/Restaurant',
            'status' => 'actif',
            'password' => Hash::make('password'),
            'identifiant_unique_somacif' => 'SOM-AZALAI1',
        ]);

        // On crée un point de vente pour ce client
        PointDeVente::create([
            'responsable_id' => $client1->id,
            'nom' => 'Azalaï Dépôt Principal',
            'adresse' => 'ACI 2000, Bamako',
            'telephone' => '+223 20 22 80 44',
            // --- LA CORRECTION EST ICI ---
            // On ajoute une valeur par défaut pour le type.
            'type' => 'Principale', 
        ]);

        // Création du deuxième client de test
        $client2 = Client::create([
            'nom' => 'Client Test Sogoniko',
            'email' => 'client@sogoniko.com',
            'telephone' => '+22370000002',
            'type' => 'Grossiste',
            'status' => 'actif',
            'password' => Hash::make('password'),
            'identifiant_unique_somacif' => 'SOM-SOGO02',
        ]);

        // On crée aussi un point de vente pour ce client
        PointDeVente::create([
            'responsable_id' => $client2->id,
            'nom' => 'Dépôt Central Sogoniko',
            'adresse' => 'Grand Marché, Sogoniko',
            'telephone' => '+223 20 23 50 50',
             // --- LA CORRECTION EST ICI ---
            'type' => 'Principale',
        ]);
    }
}