<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentaireResource;
use App\Models\Publication;
use App\Services\PublicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentaireController extends Controller
{
    public function store(Request $request, Publication $publication, PublicationService $publications): JsonResponse
    {
        $donnees = $request->validate([
            'contenu' => ['required', 'string', 'max:2000'],
            'lang' => ['nullable', Rule::in(['fr', 'en'])],
        ]);

        $commentaire = $publications->commenter(
            $publication,
            $request->user(),
            $donnees['contenu'],
            $donnees['lang'] ?? null,
        );

        $commentaire->load('utilisateur');

        return (new CommentaireResource($commentaire))
            ->response()
            ->setStatusCode(201);
    }
}
