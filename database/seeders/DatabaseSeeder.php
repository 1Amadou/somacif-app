<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // On s'assure qu'il n'y a pas de doublon d'admin
        if (User::where('email', 'admin@somacif.com')->doesntExist()) {
            User::factory()->create([
                'name' => 'Admin Somacif',
                'email' => 'admin@somacif.com',
                'password' => Hash::make('password'), // Mot de passe par défaut : password
            ]);
        }
        
        // Exécution des seeders dans un ordre logique et maîtrisé
        $this->call([
            FournisseurSeeder::class,
            ProductSeeder::class,
            ClientSeeder::class,
            LivreurSeeder::class,
            PageSeeder::class,
            VenteDirecteSetupSeeder::class
            
        ]);
    }
}













// public function run(): void
//     {
//         $this->call([
//             UserSeeder::class,
//             CategorySeeder::class,
//             // PointDeVenteSeeder::class,
//             ClientSeeder::class,
//             ProductSeeder::class,
//             PostSeeder::class,
//             PageSeeder::class,
//             NotificationTemplateSeeder::class, 
//             FournisseurSeeder::class,
//             LivreurSeeder::class,
//         ]);
//     }