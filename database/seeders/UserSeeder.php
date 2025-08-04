<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création de l'administrateur principal
        User::firstOrCreate(
            ['email' => 'admin@somacif.com'],
            [
                'name' => 'Admin SOMACIF',
                'password' => Hash::make('password'), // Mot de passe : password
            ]
        );
    }
}