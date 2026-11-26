<?php

namespace Database\Factories;

use App\Enums\Langue;
use App\Enums\PorteeVisibilite;
use App\Models\Category;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publication>
 */
class PublicationFactory extends Factory
{
    protected $model = Publication::class;

    public function definition(): array
    {
        return [
            'id_utilisateur' => User::factory(),
            'id_categorie' => Category::query()->inRandomOrder()->value('id_categorie')
                ?? Category::factory(),
            'id_quartier' => null,
            'id_ville' => null,
            'id_pays' => null,
            'titre' => fake()->text(100),
            'description' => fake()->paragraph(),
            'prix' => fake()->optional()->randomFloat(2, 10, 150),
            'date_creation' => now(),
            'statut' => 'active',
            'lang' => Langue::FR,
            'portee' => PorteeVisibilite::QUARTIER,
            'version' => 1,
        ];
    }

    public function nonPublie(): static
    {
        return $this->state(fn () => [
            'statut' => 'inactif',
        ]);
    }
}
