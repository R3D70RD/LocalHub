<?php

namespace Database\Factories;

use App\Enums\TypeReaction;
use App\Models\Publication;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reaction>
 */
class ReactionFactory extends Factory
{
    protected $model = Reaction::class;

    public function definition(): array
    {
        return [
            'id_publication' => Publication::factory(),
            'id_utilisateur' => User::factory(),
            'type' => TypeReaction::JAIME,
        ];
    }
}
