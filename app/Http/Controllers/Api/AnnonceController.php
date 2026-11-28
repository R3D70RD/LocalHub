<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnregistrerAnnonceRequest;
use App\Http\Resources\AnnonceResource;
use App\Models\Annonce;
use App\Services\AnnonceService;
use App\Services\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class AnnonceController extends Controller
{
    public function __construct(
        private readonly AnnonceService $annonces,
        private readonly FeedService $feed,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $annonces = $this->annonces->liste(Auth::guard('sanctum')->user(), [
            'scope' => $request->query('scope'),
            'q' => $request->query('q'),
            'par_page' => (int) $request->query('par_page', 15),
        ]);

        return AnnonceResource::collection($annonces);
    }

    public function show(Request $request, Annonce $annonce): AnnonceResource
    {
        abort_unless($this->feed->peutVoirEntite(Auth::guard('sanctum')->user(), $annonce), 404);

        $annonce->load(['utilisateur', 'categorie', 'offres.utilisateur', 'messages.expediteur']);

        return new AnnonceResource($annonce);
    }

    public function store(EnregistrerAnnonceRequest $request): JsonResponse
    {
        $this->authorize('create', Annonce::class);

        $annonce = $this->annonces->creer($request->user(), $request->validated());
        $annonce->load(['utilisateur', 'categorie']);

        return (new AnnonceResource($annonce))
            ->response()
            ->setStatusCode(201);
    }

    public function update(EnregistrerAnnonceRequest $request, Annonce $annonce): AnnonceResource
    {
        $this->authorize('update', $annonce);

        $annonce = $this->annonces->mettreAJour($annonce, $request->validated());
        $annonce->load(['utilisateur', 'categorie']);

        return new AnnonceResource($annonce);
    }

    public function destroy(Annonce $annonce): JsonResponse
    {
        $this->authorize('delete', $annonce);

        $this->annonces->supprimer($annonce);

        return response()->json(null, 204);
    }
}
