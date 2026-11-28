<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnregistrerDemandeAideRequest;
use App\Http\Resources\DemandeAideResource;
use App\Models\DemandeAide;
use App\Services\DemandeAideService;
use App\Services\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class DemandeAideController extends Controller
{
    public function __construct(
        private readonly DemandeAideService $demandes,
        private readonly FeedService $feed,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $demandes = $this->demandes->liste(Auth::guard('sanctum')->user(), [
            'scope' => $request->query('scope'),
            'q' => $request->query('q'),
            'par_page' => (int) $request->query('par_page', 15),
        ]);

        return DemandeAideResource::collection($demandes);
    }

    public function show(Request $request, DemandeAide $demande): DemandeAideResource
    {
        abort_unless($this->feed->peutVoirEntite(Auth::guard('sanctum')->user(), $demande), 404);

        $demande->load(['utilisateur', 'categorie', 'offres.utilisateur']);

        return new DemandeAideResource($demande);
    }

    public function store(EnregistrerDemandeAideRequest $request): JsonResponse
    {
        $this->authorize('create', DemandeAide::class);

        $demande = $this->demandes->creer($request->user(), $request->validated());
        $demande->load(['utilisateur', 'categorie']);

        return (new DemandeAideResource($demande))
            ->response()
            ->setStatusCode(201);
    }

    public function update(EnregistrerDemandeAideRequest $request, DemandeAide $demande): DemandeAideResource
    {
        $this->authorize('update', $demande);

        $demande = $this->demandes->mettreAJour($demande, $request->validated());
        $demande->load(['utilisateur', 'categorie']);

        return new DemandeAideResource($demande);
    }

    public function destroy(DemandeAide $demande): JsonResponse
    {
        $this->authorize('delete', $demande);

        $this->demandes->supprimer($demande);

        return response()->json(null, 204);
    }
}
