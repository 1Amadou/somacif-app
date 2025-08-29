<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $nom = $this->faker->unique()->words(2, true);
        return [
            'nom' => Str::title($nom),
            'slug' => Str::slug($nom),
            'description_courte' => $this->faker->sentence(),
            'description_longue' => $this->faker->paragraph(3),
            'is_visible' => true,
        ];
    }
}