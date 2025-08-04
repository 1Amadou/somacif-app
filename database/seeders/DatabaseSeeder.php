<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class, // Troncature des catégories et posts
            PointDeVenteSeeder::class, // Troncature des points de vente et inventaire
            ClientSeeder::class,
            ProductSeeder::class, // Troncature des produits et unités
            PostSeeder::class, // Remplissage des posts
            PageSeeder::class,
        ]);
    }
}