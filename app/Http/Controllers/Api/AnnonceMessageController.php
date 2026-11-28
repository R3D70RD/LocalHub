<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\Annonce;
use App\Services\AnnonceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnonceMessageController extends Controller
{
    public function store(Request $request, Annonce $annonce, AnnonceService $annonces): JsonResponse
    {
        $donnees = $request->validate([
            'contenu' => ['required', 'string', 'max:2000'],
        ]);

        $message = $annonces->envoyerMessage($annonce, $request->user(), $donnees['contenu']);
        $message->load('expediteur');

        return (new MessageResource($message))
            ->response()
            ->setStatusCode(201);
    }
}
