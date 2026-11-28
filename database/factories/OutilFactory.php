<?php

namespace Database\Factories;

use App\Enums\PorteeVisibilite;
use App\Models\Outil;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Outil>
 */
class OutilFactory extends Factory
{
    protected $model = Outil::class;

    public function definition(): array
    {
        return [
            'id_proprietaire' => User::factory(),
            'titre' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'portee' => PorteeVisibilite::QUARTIER,
            'etat' => 'bon',
            'disponible' => true,
            'statut' => 'actif',
            'version' => 1,
        ];
    }
}
