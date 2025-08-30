<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\UniteDeVente;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        UniteDeVente::truncate();
        Product::truncate();
        Schema::enableForeignKeyConstraints();

        // --- PRODUIT 1 : Tilapia ---
        $tilapia = Product::create([
            'nom' => 'Tilapia',
            'slug' => 'tilapia',
            'description_courte' => 'Poisson d\'eau douce populaire, à la chair ferme et savoureuse.',
            'is_visible' => true,
        ]);

        UniteDeVente::create([
            'product_id' => $tilapia->id,
            'nom_unite' => 'Carton',
            'calibre' => 'Moyen (200-300g)',
            'stock' => 0,
            'prix_particulier' => 15000,
            'prix_grossiste' => 13500,
            'prix_hotel_restaurant' => 14000,
        ]);
        
        // ... (le reste du fichier ne change pas)
        UniteDeVente::create([
            'product_id' => $tilapia->id,
            'nom_unite' => 'Carton',
            'calibre' => 'Gros (300-500g)',
            'stock' => 0,
            'prix_particulier' => 18000,
            'prix_grossiste' => 16500,
            'prix_hotel_restaurant' => 17000,
        ]);


        // --- PRODUIT 2 : Poulet Entier ---
        $poulet = Product::create([
            'nom' => 'Poulet Entier',
            'slug' => 'poulet-entier',
            'description_courte' => 'Poulet de chair entier, idéal pour rôtir ou braiser.',
            'is_visible' => true,
        ]);

        UniteDeVente::create([
            'product_id' => $poulet->id,
            'nom_unite' => 'Carton de 10kg',
            'calibre' => '1.2kg par pièce',
            'stock' => 0,
            'prix_particulier' => 25000,
            'prix_grossiste' => 22000,
            'prix_hotel_restaurant' => 23000,
        ]);


        // --- PRODUIT 3 : Frites ---
        $frites = Product::create([
            'nom' => 'Frites Surgelées',
            'slug' => 'frites-surgelees',
            'description_courte' => 'Frites pré-découpées et surgelées, prêtes à frire.',
            'is_visible' => true,
        ]);

        UniteDeVente::create([
            'product_id' => $frites->id,
            'nom_unite' => 'Sachet',
            'calibre' => '2.5kg',
            'stock' => 0,
            'prix_particulier' => 5000,
            'prix_grossiste' => 4000,
            'prix_hotel_restaurant' => 4200,
        ]);
    }
}