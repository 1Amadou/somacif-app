<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PointDeVente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PointDeVenteSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        PointDeVente::truncate();
        DB::table('inventory')->truncate(); // On vide aussi la table des stocks
        Schema::enableForeignKeyConstraints();
        
        PointDeVente::create([
            'nom' => 'SOMACIF - Siège Social',
            'type' => 'Principal',
            'adresse' => 'Halle de Bamako, Quartier du Fleuve, Bamako',
            'telephone' => '20221111',
            'horaires' => 'Lundi - Samedi : 08h00 - 18h00',
            'Maps_link' => 'https://maps.google.com/'
        ]);

        PointDeVente::create([
            'nom' => 'SOMACIF - ACI 2000',
            'type' => 'Secondaire',
            'adresse' => 'Près de l\'Hôtel Radisson Blu, Hamdallaye ACI 2000, Bamako',
            'telephone' => '20221122',
            'horaires' => 'Lundi - Dimanche : 09h00 - 20h00',
            'Maps_link' => 'https://maps.google.com/'
        ]);

        PointDeVente::create([
            'nom' => 'SOMACIF - Badalabougou',
            'type' => 'Secondaire',
            'adresse' => 'Avenue de l\'OUA, près du Palais de la Culture, Bamako',
            'telephone' => '20221133',
            'horaires' => 'Lundi - Samedi : 09h00 - 19h00',
            'Maps_link' => 'https://maps.google.com/'
        ]);
    }
}