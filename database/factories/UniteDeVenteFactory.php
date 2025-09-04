<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\UniteDeVente;
use Illuminate\Database\Eloquent\Factories\Factory;

class UniteDeVenteFactory extends Factory
{
    protected $model = UniteDeVente::class;

    public function definition(): array
    {
        return [
            'nom_unite' => $this->faker->unique()->word,
            'calibre' => $this->faker->randomElement(['Petit', 'Moyen', 'Gros']),
            'prix_unitaire' => $this->faker->numberBetween(10000, 50000),
            'prix_grossiste' => $this->faker->numberBetween(9000, 45000),
            'prix_hotel_restaurant' => $this->faker->numberBetween(9500, 47000),
            'prix_particulier' => $this->faker->numberBetween(11000, 55000),
            'stock' => 0,
            'product_id' => Product::factory(),
        ];
    }
}