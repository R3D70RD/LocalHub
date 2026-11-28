<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Services\FeedService;
use App\Services\TraductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TraductionController extends Controller
{
    public function __construct(
        private readonly TraductionService $traductions,
        private readonly FeedService $feed,
    ) {}

    /**
     * Traduit à la demande le contenu d'une publication visible.
     *
     * Le texte source est lu côté serveur (jamais fourni par le client) : la
     * traduction porte donc uniquement sur du contenu réel et respecte la
     * règle de visibilité. Le résultat n'est pas persisté.
     */
    public function traduirePublication(Request $request, Publication $publication): JsonResponse
    {
        abort_unless($this->feed->peutVoir($request->user(), $publication), 404);
        abort_unless($this->traductions->estDisponible(), 503);

        $donnees = $request->validate([
            'cible' => ['required', Rule::in(TraductionService::LANGUES)],
        ]);

        return response()->json([
            'traduction' => $this->traductions->traduire(
                $publication->description,
                'auto',
                $donnees['cible'],
            ),
        ]);
    }
}
