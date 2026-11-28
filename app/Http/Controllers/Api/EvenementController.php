<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnregistrerEvenementRequest;
use App\Http\Resources\EvenementResource;
use App\Models\Evenement;
use App\Services\EvenementService;
use App\Services\FeedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class EvenementController extends Controller
{
    public function __construct(
        private readonly EvenementService $evenements,
        private readonly FeedService $feed,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $evenements = $this->evenements->liste(Auth::guard('sanctum')->user(), [
            'scope' => $request->query('scope'),
            'q' => $request->query('q'),
            'par_page' => (int) $request->query('par_page', 15),
        ]);

        return EvenementResource::collection($evenements);
    }

    public function show(Request $request, Evenement $evenement): EvenementResource
    {
        abort_unless($this->feed->peutVoirEntite(Auth::guard('sanctum')->user(), $evenement), 404);

        $evenement->load(['utilisateur', 'categorie', 'inscriptions.utilisateur']);

        return new EvenementResource($evenement);
    }

    public function store(EnregistrerEvenementRequest $request): JsonResponse
    {
        $this->authorize('create', Evenement::class);

        $evenement = $this->evenements->creer($request->user(), $request->validated());
        $evenement->load(['utilisateur', 'categorie']);

        return (new EvenementResource($evenement))
            ->response()
            ->setStatusCode(201);
    }

    public function update(EnregistrerEvenementRequest $request, Evenement $evenement): EvenementResource
    {
        $this->authorize('update', $evenement);

        $evenement = $this->evenements->mettreAJour($evenement, $request->validated());
        $evenement->load(['utilisateur', 'categorie']);

        return new EvenementResource($evenement);
    }

    public function destroy(Evenement $evenement): JsonResponse
    {
        $this->authorize('delete', $evenement);

        $this->evenements->supprimer($evenement);

        return response()->json(null, 204);
    }
}
