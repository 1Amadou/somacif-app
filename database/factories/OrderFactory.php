<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Livreur;
use App\Models\PointDeVente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'point_de_vente_id' => PointDeVente::factory(),
            'livreur_id' => null, // Par défaut, pas de livreur assigné
            'numero_commande' => 'CMD-' . $this->faker->unique()->numberBetween(1000, 9999),
            'statut' => 'en_attente',
            'montant_total' => $this->faker->randomFloat(2, 10000, 200000),
            'notes' => $this->faker->sentence(),
            'statut_paiement' => 'non_payee',
            'montant_paye' => 0,
            'is_vente_directe' => false,
        ];
    }
}