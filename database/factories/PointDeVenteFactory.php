<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class PointDeVenteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => 'Point de Vente ' . $this->faker->unique()->company(),
            'type' => $this->faker->randomElement(['Principal', 'Secondaire', 'Partenaire']),
            'adresse' => $this->faker->address(),
            'telephone' => $this->faker->phoneNumber(),
            
           
            // On lie le responsable et le client au même client par défaut.
            'client_id' => Client::factory(),
            'responsable_id' => function (array $attributes) {
                return $attributes['client_id'];
            },
        ];
    }
}