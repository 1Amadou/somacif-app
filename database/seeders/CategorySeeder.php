<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Schema;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Category::truncate();
        Post::truncate(); // On vide aussi la table des articles
        Schema::enableForeignKeyConstraints();

        Category::firstOrCreate(['slug' => 'actualites-entreprise'], ['nom' => 'Actualités de l\'entreprise']);
        Category::firstOrCreate(['slug' => 'conseils-conservation'], ['nom' => 'Conseils & Recettes']);
        Category::firstOrCreate(['slug' => 'promotions'], ['nom' => 'Annonces SOMACIF']);
    }
}