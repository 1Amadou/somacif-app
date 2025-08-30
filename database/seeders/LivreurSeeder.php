<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Livreur;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class LivreurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Livreur::truncate();
        Schema::enableForeignKeyConstraints();

        Livreur::create([
            'prenom' => 'Samba',
            'nom' => 'Diallo',
            'telephone' => '76010203',
            'email' => 'samba.diallo@example.com',
            'password' => Hash::make('password'),
        ]);

        Livreur::create([
            'prenom' => 'Ousmane',
            'nom' => 'Coulibaly',
            'telephone' => '76040506',
            'email' => 'ousmane.coulibaly@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
