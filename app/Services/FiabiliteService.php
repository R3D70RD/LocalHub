<?php

namespace App\Services;

use App\Models\AvisPret;
use App\Models\User;

/**
 * Calcule le score de fiabilité d'un utilisateur (0–100) à partir des avis
 * reçus après un prêt. Sans avis, l'utilisateur conserve le score neutre 100.
 */
class FiabiliteService
{
    public function recalculer(User $utilisateur): int
    {
        $moyenne = AvisPret::query()
            ->where('id_cible', $utilisateur->id_utilisateur)
            ->avg('note');

        $score = $moyenne === null
            ? 100
            : (int) round(100 - (5 - (float) $moyenne) * 15);

        $score = max(0, min(100, $score));

        $utilisateur->forceFill(['fiabilite' => $score])->save();

        return $score;
    }
}
