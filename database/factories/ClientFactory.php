<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->name(),
            'type' => $this->faker->randomElement(['Grossiste', 'Hôtel/Restaurant', 'Particulier']),
            'telephone' => $this->faker->unique()->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'identifiant_unique_somacif' => 'SOM-' . Str::upper(Str::random(8)),
            'password' => static::$password ??= Hash::make('password'),
        ];
    }
}