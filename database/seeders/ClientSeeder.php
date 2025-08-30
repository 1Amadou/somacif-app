<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\PointDeVente;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        PointDeVente::truncate();
        Client::truncate();
        Schema::enableForeignKeyConstraints();

        // --- CLIENT 1 : Hôtel ---
        $hotelAzalai = Client::create([
            'nom' => 'Hôtel Azalaï',
            'type' => 'Hôtel/Restaurant',
            'telephone' => '+223 20 22 80 44',
            'email' => 'reception.bamako@azalaihotels.com',
            'password' => Hash::make('password'),
            'identifiant_unique_somacif' => 'SOM-CLI-001', // <-- CORRECTION ICI
        ]);

        PointDeVente::create([
            'responsable_id' => $hotelAzalai->id,
            'nom' => 'Azalaï Dépôt Principal',
            'adresse' => 'ACI 2000, Bamako',
            'telephone' => '+223 20 22 80 44',
        ]);
        
        // --- CLIENT 2 : Grossiste ---
        $grossisteSogoniko = Client::create([
            'nom' => 'Supermarché Sogoniko',
            'type' => 'Grossiste',
            'telephone' => '+223 76 10 20 30',
            'email' => 'achats@sogoniko.ml',
            'password' => Hash::make('password'),
            'identifiant_unique_somacif' => 'SOM-CLI-002', // <-- CORRECTION ICI
        ]);

        PointDeVente::create([
            'responsable_id' => $grossisteSogoniko->id,
            'nom' => 'Dépôt Central Sogoniko',
            'adresse' => 'Marché Sogoniko, Bamako',
            'telephone' => '+223 76 10 20 30',
        ]);
    }
}