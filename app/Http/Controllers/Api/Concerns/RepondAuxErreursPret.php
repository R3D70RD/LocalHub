<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Exceptions\ActionNonAutoriseeException;
use App\Exceptions\ConflitPretException;
use App\Exceptions\TransitionInvalideException;
use Illuminate\Http\JsonResponse;
use Throwable;

trait RepondAuxErreursPret
{
    protected function reponseErreur(Throwable $exception): JsonResponse
    {
        [$statut, $code] = match (true) {
            $exception instanceof ConflitPretException => [409, 'pret_conflit'],
            $exception instanceof ActionNonAutoriseeException => [403, 'pret_non_autorise'],
            $exception instanceof TransitionInvalideException => [422, 'pret_transition_invalide'],
            default => [null, null],
        };

        if ($statut === null) {
            throw $exception;
        }

        return response()->json([
            'message' => $exception->getMessage(),
            'code' => $code,
        ], $statut);
    }
}
