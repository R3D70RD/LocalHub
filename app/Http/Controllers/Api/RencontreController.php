<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnregistrerRencontreRequest;
use App\Http\Resources\RencontreResource;
use App\Models\Rencontre;
use App\Services\FeedService;
use App\Services\RencontreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class RencontreController extends Controller
{
    public function __construct(
        private readonly RencontreService $rencontres,
        private readonly FeedService $feed,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $rencontres = $this->rencontres->liste(Auth::guard('sanctum')->user(), [
            'scope' => $request->query('scope'),
            'q' => $request->query('q'),
            'par_page' => (int) $request->query('par_page', 15),
        ]);

        return RencontreResource::collection($rencontres);
    }

    public function show(Request $request, Rencontre $rencontre): RencontreResource
    {
        abort_unless($this->feed->peutVoirEntite(Auth::guard('sanctum')->user(), $rencontre), 404);

        $rencontre->load(['utilisateur', 'categorie', 'membres.utilisateur']);

        return new RencontreResource($rencontre);
    }

    public function store(EnregistrerRencontreRequest $request): JsonResponse
    {
        $this->authorize('create', Rencontre::class);

        $rencontre = $this->rencontres->creer($request->user(), $request->validated());
        $rencontre->load(['utilisateur', 'categorie']);

        return (new RencontreResource($rencontre))
            ->response()
            ->setStatusCode(201);
    }

    public function update(EnregistrerRencontreRequest $request, Rencontre $rencontre): RencontreResource
    {
        $this->authorize('update', $rencontre);

        $rencontre = $this->rencontres->mettreAJour($rencontre, $request->validated());
        $rencontre->load(['utilisateur', 'categorie']);

        return new RencontreResource($rencontre);
    }

    public function destroy(Rencontre $rencontre): JsonResponse
    {
        $this->authorize('delete', $rencontre);

        $this->rencontres->supprimer($rencontre);

        return response()->json(null, 204);
    }
}
