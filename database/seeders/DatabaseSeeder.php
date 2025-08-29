<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            PointDeVenteSeeder::class,
            ClientSeeder::class,
            ProductSeeder::class,
            PostSeeder::class,
            PageSeeder::class,
            NotificationTemplateSeeder::class, 
            FournisseurSeeder::class,
        ]);
    }
}