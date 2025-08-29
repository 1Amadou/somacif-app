<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UniteDeVenteFactory extends Factory
{
    public function definition(): array
    {
        $prix = $this->faker->numberBetween(10000, 25000);
        return [
            // Le product_id sera fourni par le seeder
            'nom_unite' => 'Carton 10kg',
            'calibre' => $this->faker->randomElement(['Petit', 'Moyen', 'Gros']),
            'prix_unitaire' => $prix,
            'prix_grossiste' => $prix * 0.9, // 10% de réduction pour les grossistes
            'prix_hotel_restaurant' => $prix * 0.95, // 5% de réduction
            'prix_particulier' => $prix * 1.1, // 10% de majoration
            'stock' => 0, // Le stock est initialement à 0, il sera ajouté par les arrivages
        ];
    }
}