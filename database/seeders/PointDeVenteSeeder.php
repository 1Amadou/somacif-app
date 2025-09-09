<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\PointDeVente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VenteDirecteSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a generic client for direct sales
        $clientVenteDirecte = Client::create([
            'nom' => 'Client Vente Directe',
            'email' => 'ventedirecte@somacif.com',
            'telephone' => '00000000',
            'type' => 'Particulier',
            'status' => 'actif',
            'password' => Hash::make('password'),
            'identifiant_unique_somacif' => 'SOM-VENTEDIRECTE',
        ]);

        // Create a specific Point of Sale for this client
        PointDeVente::create([
            'responsable_id' => $clientVenteDirecte->id,
            'nom' => 'Entrepôt Principal (Ventes Directes)',
            'adresse' => 'CLient passant',
            // --- THE CORRECTION IS HERE ---
            // Add a default value for the type.
            'type' => 'Comptoir',
        ]);
    }
}