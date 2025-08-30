<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\UniteDeVente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'unite_de_vente_id' => UniteDeVente::factory(),
            'quantite' => $this->faker->numberBetween(1, 10),
            'prix_unitaire' => $this->faker->randomFloat(2, 10000, 50000),
            // CORRECTION : La ligne 'prix_total' a été supprimée car la colonne n'existe pas.
        ];
    }
}