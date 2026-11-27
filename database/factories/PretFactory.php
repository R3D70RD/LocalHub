<?php

namespace Database\Factories;

use App\Enums\StatutPret;
use App\Models\Outil;
use App\Models\Pret;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pret>
 */
class PretFactory extends Factory
{
    protected $model = Pret::class;

    public function definition(): array
    {
        $debut = now()->addDay();
        $fin = now()->addDays(7);

        return [
            'id_outil' => Outil::factory(),
            'id_emprunteur' => User::factory(),
            'id_proprietaire' => User::factory(),
            'statut' => StatutPret::DEMANDE,
            'date_debut' => $debut->toDateString(),
            'date_fin' => $fin->toDateString(),
            'date_demande' => now(),
            'version' => 1,
            'cle_active' => null,
        ];
    }
}
