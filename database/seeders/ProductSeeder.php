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
        UniteDeVente::truncate(); // On vide aussi les unités de vente
        Schema::enableForeignKeyConstraints();

        $productsData = [
            ['nom' => 'Tilapia (Carpe)', 'slug' => 'tilapia-carpe', 'desc' => 'Le favori des familles maliennes, disponible dans tous les calibres.', 'calibres' => ['200-300g', '300-500g']],
            ['nom' => 'Pangasius (Filets)', 'slug' => 'pangasius-filets', 'desc' => 'Filets sans arêtes, parfaits pour les restaurants et une cuisine rapide.', 'calibres' => ['170-220g']],
            ['nom' => 'Chinchard', 'slug' => 'chinchard', 'desc' => 'Un goût prononcé et une excellente source de protéines, très populaire.', 'calibres' => ['Taille Moyenne', 'Grande Taille']],
            ['nom' => 'Dorade Royale', 'slug' => 'dorade-royale', 'desc' => 'Chair fine et savoureuse, le choix de prédilection des grands hôtels.', 'calibres' => ['400-600g']],
            ['nom' => 'Thiof (Mérou)', 'slug' => 'thiof-merou', 'desc' => 'Un poisson noble à la texture ferme, parfait pour les plats en sauce.', 'calibres' => ['Pièce 1-2kg', 'Pièce 2-3kg']],
            ['nom' => 'Capitaine', 'slug' => 'capitaine', 'desc' => 'Très apprécié pour sa chair blanche et tendre, excellent en friture ou grillé.', 'calibres' => ['Taille Moyenne']],
        ];

        foreach ($productsData as $data) {
            $product = Product::create([
                'nom' => $data['nom'],
                'slug' => $data['slug'],
                'description_courte' => $data['desc'],
                'calibres' => $data['calibres'],
                'is_visible' => true,
            ]);

            $product->uniteDeVentes()->create([
                'nom_unite' => 'Carton',
                'prix_grossiste' => rand(100, 200) * 100,
                'prix_hotel_restaurant' => rand(120, 220) * 100,
                'prix_particulier' => rand(150, 250) * 100,
            ]);
        }
    }
}