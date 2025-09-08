<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ArrivageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fournisseur_id' => \App\Models\Fournisseur::factory(),
            'numero_bon_livraison' => 'BL-' . $this->faker->unique()->numberBetween(1000, 9999),
            'date_arrivage' => $this->faker->date(),
            'notes' => $this->faker->sentence(),
            'user_id' => \App\Models\User::factory(),
            'details_produits' => '[]', // <-- AJOUTEZ CETTE LIGNE
        ];
    }
}