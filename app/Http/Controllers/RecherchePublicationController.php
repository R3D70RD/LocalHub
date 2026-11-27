<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Services\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecherchePublicationController extends Controller
{
    public function __construct(private readonly FeedService $feed) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:50'],
        ]);

        $requete = Publication::query()
            ->whereNotNull('date_creation')
            ->where('statut', 'active')
            ->where('titre', 'like', '%'.$data['q'].'%')
            ->orderBy('titre')
            ->limit(8);

        // Réutilise la règle du fil : les visiteurs ne voient que le pays,
        // les utilisateurs connectés conservent tous leurs niveaux autorisés.
        $this->feed->appliquerPortee($requete, $request->user(), 'pays');

        $publications = $requete->get(['id_publication', 'titre']);

        return response()->json([
            'data' => $publications->map(fn (Publication $publication) => [
                'id' => $publication->id_publication,
                'label' => $publication->titre,
                'url' => route('publications.show', $publication),
            ]),
        ]);
    }
}
