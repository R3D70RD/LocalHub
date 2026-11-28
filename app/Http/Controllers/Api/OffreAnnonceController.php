<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OffreAnnonceResource;
use App\Models\Annonce;
use App\Services\AnnonceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OffreAnnonceController extends Controller
{
    public function store(Request $request, Annonce $annonce, AnnonceService $annonces): JsonResponse
    {
        $donnees = $request->validate([
            'montant' => ['nullable', 'numeric', 'min:0'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $offre = $annonces->proposerOffre($annonce, $request->user(), $donnees);
        $offre->load('utilisateur');

        return (new OffreAnnonceResource($offre))
            ->response()
            ->setStatusCode(201);
    }
}
