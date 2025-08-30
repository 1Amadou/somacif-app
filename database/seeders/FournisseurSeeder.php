<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Fournisseur;
use Illuminate\Support\Facades\Schema;

class FournisseurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Fournisseur::truncate();
        Schema::enableForeignKeyConstraints();

        Fournisseur::create([
            'nom_entreprise' => 'Poisson Frais SA',
            'nom_contact' => 'Moussa Traoré',
            'email_contact' => 'contact@poissonfrais.com',
            'telephone_contact' => '+223 76 00 00 01',
            'adresse' => 'Zone Industrielle, Bamako',
            'notes' => 'Fournisseur principal de poissons et fruits de mer.',
        ]);

        Fournisseur::create([
            'nom_entreprise' => 'Volaille du Mandé',
            'nom_contact' => 'Awa Diarra',
            'email_contact' => 'contact@volailledumande.ml',
            'telephone_contact' => '+223 76 00 00 02',
            'adresse' => 'Route de Ségou, Bamako',
            'notes' => 'Spécialisé dans les poulets et autres volailles.',
        ]);
        
        Fournisseur::create([
            'nom_entreprise' => 'Frites d\'Or',
            'nom_contact' => 'Jean-Pierre Dubois',
            'email_contact' => 'jp.dubois@fritesdor.eu',
            'telephone_contact' => '+33 6 00 00 00 03',
            'adresse' => 'Bruxelles, Belgique',
            'notes' => 'Importateur de frites surgelées de qualité supérieure.',
        ]);
    }
}