<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InscriptionEvenementResource;
use App\Models\Evenement;
use App\Services\EvenementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InscriptionEvenementController extends Controller
{
    public function store(Request $request, Evenement $evenement, EvenementService $evenements): JsonResponse
    {
        $inscription = $evenements->inscrire($evenement, $request->user());
        $inscription->load('utilisateur');

        return (new InscriptionEvenementResource($inscription))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request, Evenement $evenement, EvenementService $evenements): JsonResponse
    {
        $evenements->annulerInscription($evenement, $request->user());

        return response()->json(null, 204);
    }
}
