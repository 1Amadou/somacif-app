<?php

namespace Database\Factories;

use App\Models\Fournisseur;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Arrivage>
 */
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
            'fournisseur_id' => Fournisseur::factory(),
            'user_id' => User::factory(),
            'numero_bon_livraison' => $this->faker->unique()->numerify('BL-#####'),
            'date_arrivage' => $this->faker->date(),
            'details_produits' => [], // Par défaut, vide. On le remplira dans le test.
            'notes' => $this->faker->sentence(),
        ];
    }
}