<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PointDeVente>
 */
class PointDeVenteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => 'Point de Vente ' . $this->faker->city,
            'type' => $this->faker->randomElement(['Principal', 'Secondaire', 'Partenaire']),
            'adresse' => $this->faker->address,
            'telephone' => $this->faker->phoneNumber,
        ];
    }
}