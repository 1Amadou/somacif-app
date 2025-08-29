<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fournisseur>
 */
class FournisseurFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom_entreprise' => $this->faker->company() . ' ' . $this->faker->companySuffix(),
            'nom_contact' => $this->faker->name(),
            'telephone_contact' => $this->faker->unique()->phoneNumber(),
            'email_contact' => $this->faker->unique()->safeEmail(),
            'adresse' => $this->faker->address(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}