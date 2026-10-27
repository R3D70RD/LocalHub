<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+1 month');
        $endDate = (clone $startDate)->modify('+' . fake()->numberBetween(1, 7) . ' days');

        return [
            'id_publication' => Publication::factory(),
            'id_demandeur' => User::factory(),
            'id_proprietaire' => User::factory(),
            'date_debut' => $startDate->format('Y-m-d'),
            'date_fin' => $endDate->format('Y-m-d'),
            'statut' => 'en_attente', 
            'message' => fake()->optional()->sentence(10), // Optional short message
        ];
    }

    public function annulee(): static
    {
        return $this->state(fn () => [
            'statut' => 'annulee', 
        ]);
    }
}