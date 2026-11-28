<?php

namespace Database\Factories;

use App\Models\Commentaire;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commentaire>
 */
class CommentaireFactory extends Factory
{
    protected $model = Commentaire::class;

    public function definition(): array
    {
        return [
            'id_publication' => Publication::factory(),
            'id_utilisateur' => User::factory(),
            'contenu' => fake()->sentence(),
        ];
    }
}
