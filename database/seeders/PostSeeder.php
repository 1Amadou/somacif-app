<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\Category;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // On ne met PAS de truncate ici car CategorySeeder s'en occupe déjà.
        
        $catAnnonces = Category::where('slug', 'promotions')->first();
        $catConseils = Category::where('slug', 'conseils-conservation')->first();

        if ($catAnnonces) {
            Post::create([
                'category_id' => $catAnnonces->id,
                'titre' => 'Arrivage Exceptionnel : Le Thiof de haute mer est disponible',
                'slug' => 'arrivage-exceptionnel-thiof',
                'contenu' => 'Découvrez notre dernier arrivage de Thiof, un poisson d\'exception pour les grandes occasions...',
                'date_publication' => now()->subDays(5),
                'is_published' => true,
            ]);
        }

        if ($catConseils) {
            Post::create([
                'category_id' => $catConseils->id,
                'titre' => 'Comment réussir une dorade en croûte de sel ?',
                'slug' => 'comment-reussir-dorade-croute-de-sel',
                'contenu' => 'La cuisson en croûte de sel est une technique ancestrale qui sublime la saveur délicate de la dorade...',
                'date_publication' => now()->subDays(10),
                'is_published' => true,
            ]);
        }
    }
}