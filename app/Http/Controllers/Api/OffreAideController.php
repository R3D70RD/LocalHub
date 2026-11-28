<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OffreAideResource;
use App\Models\DemandeAide;
use App\Services\DemandeAideService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OffreAideController extends Controller
{
    public function store(Request $request, DemandeAide $demande, DemandeAideService $demandes): JsonResponse
    {
        $donnees = $request->validate([
            'titre' => ['nullable', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $offre = $demandes->proposerOffre($demande, $request->user(), $donnees);
        $offre->load('utilisateur');

        return (new OffreAideResource($offre))
            ->response()
            ->setStatusCode(201);
    }
}
