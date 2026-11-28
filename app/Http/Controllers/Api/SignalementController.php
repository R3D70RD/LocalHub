<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SignalementResource;
use App\Services\SignalementService;
use App\Support\Cibles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SignalementController extends Controller
{
    public function store(Request $request, SignalementService $signalements): JsonResponse
    {
        $donnees = $request->validate([
            'cible_type' => ['required', Rule::in(Cibles::cles())],
            'cible_id' => ['required', 'integer'],
            'motif' => ['required', Rule::in(['spam', 'harcelement', 'contenu_illegal', 'autre'])],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $classe = Cibles::classe($donnees['cible_type']);
        $cible = $classe::query()->find($donnees['cible_id']);

        abort_if($cible === null, 404);

        $signalement = $signalements->signaler(
            $request->user(),
            $donnees['cible_type'],
            (int) $donnees['cible_id'],
            $donnees['motif'],
            $donnees['description'] ?? null,
        );

        $signalement->load(['utilisateur', 'moderateur']);

        return (new SignalementResource($signalement))
            ->response()
            ->setStatusCode(201);
    }
}
