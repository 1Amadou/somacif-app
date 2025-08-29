<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\UniteDeVente;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // On crée 10 produits principaux
        Product::factory(10)
            ->create()
            ->each(function ($product) {
                // Pour chaque produit, on crée entre 1 et 3 unités de vente (calibres)
                UniteDeVente::factory(rand(1, 3))->create([
                    'product_id' => $product->id,
                ]);
            });
    }
}