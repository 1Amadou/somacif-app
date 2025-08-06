<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\UniteDeVente;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Product::truncate();
        UniteDeVente::truncate();
        Schema::enableForeignKeyConstraints();

        $product1 = Product::create([
            'nom' => 'Tilapia (Carpe)',
            'slug' => 'tilapia-carpe',
            'description_courte' => 'Le favori des familles maliennes, disponible dans tous les calibres.',
            'is_visible' => true,
        ]);
        $product1->uniteDeVentes()->create([
            'nom_unite' => 'Carton',
            'calibre' => '200-300g',
            'prix_grossiste' => 15000,
            'prix_hotel_restaurant' => 16000,
            'prix_particulier' => 18000,
        ]);
        $product1->uniteDeVentes()->create([
            'nom_unite' => 'Carton',
            'calibre' => '300-500g',
            'prix_grossiste' => 18000,
            'prix_hotel_restaurant' => 19500,
            'prix_particulier' => 22000,
        ]);

        $product2 = Product::create([
            'nom' => 'Pangasius (Filets)',
            'slug' => 'pangasius-filets',
            'description_courte' => 'Filets sans arêtes, parfaits pour les restaurants et une cuisine rapide.',
            'is_visible' => true,
        ]);
        $product2->uniteDeVentes()->create([
            'nom_unite' => 'Carton',
            'calibre' => '170-220g',
            'prix_grossiste' => 25000,
            'prix_hotel_restaurant' => 27000,
            'prix_particulier' => 30000,
        ]);
    }
}