<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RepondAuxErreursPret;
use App\Http\Controllers\Controller;
use App\Http\Resources\AvisPretResource;
use App\Models\Pret;
use App\Services\AvisPretService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AvisPretController extends Controller
{
    use RepondAuxErreursPret;

    public function store(Request $request, Pret $pret, AvisPretService $avis): JsonResponse
    {
        $donnees = $request->validate([
            'note' => ['required', 'integer', 'min:1', 'max:5'],
            'commentaire' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $avisPret = $avis->noter($pret, $request->user(), (int) $donnees['note'], $donnees['commentaire'] ?? null);
        } catch (Throwable $exception) {
            return $this->reponseErreur($exception);
        }

        $avisPret->load(['auteur', 'cible']);

        return (new AvisPretResource($avisPret))
            ->response()
            ->setStatusCode(201);
    }
}
