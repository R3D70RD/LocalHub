<?php

namespace App\Services;

use App\Models\Idempotence;
use App\Models\User;
use Closure;
use Illuminate\Database\QueryException;

/**
 * Rejoue la réponse d'une requête déjà traitée pour une même clé
 * `Idempotency-Key`, afin qu'un retry réseau ne crée pas de doublon.
 */
class IdempotencyService
{
    /**
     * @param  Closure(): array{0: int, 1: array<string, mixed>, 2?: int}  $callback
     * @return array{rejoue: bool, statut: int, reponse: array<string, mixed>, id?: int}
     */
    public function executer(
        ?string $cle,
        ?User $utilisateur,
        string $route,
        string $methode,
        Closure $callback,
    ): array {
        if ($cle === null || $cle === '') {
            [$statut, $reponse, $id] = $callback();

            return ['rejoue' => false, 'statut' => $statut, 'reponse' => $reponse, 'id' => $id ?? null];
        }

        $hash = hash('sha256', ($utilisateur?->id_utilisateur ?? 'invite').'|'.$methode.'|'.$route.'|'.$cle);
        $existante = Idempotence::query()->where('cle', $hash)->first();

        if ($existante !== null) {
            return [
                'rejoue' => true,
                'statut' => $existante->statut,
                'reponse' => $existante->reponse ?? [],
            ];
        }

        [$statut, $reponse, $id] = $callback();

        try {
            Idempotence::create([
                'id_utilisateur' => $utilisateur?->id_utilisateur,
                'cle' => $hash,
                'route' => $route,
                'methode' => $methode,
                'statut' => $statut,
                'reponse' => $reponse,
            ]);
        } catch (QueryException $exception) {
            // Deux requêtes concurrentes avec la même clé : la première a
            // gagné, on renvoie sa réponse.
            $existante = Idempotence::query()->where('cle', $hash)->first();

            if ($existante !== null) {
                return [
                    'rejoue' => true,
                    'statut' => $existante->statut,
                    'reponse' => $existante->reponse ?? [],
                ];
            }

            throw $exception;
        }

        return ['rejoue' => false, 'statut' => $statut, 'reponse' => $reponse, 'id' => $id ?? null];
    }
}
