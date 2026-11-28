<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnregistrerPublicationRequest;
use App\Http\Resources\PublicationResource;
use App\Models\Publication;
use App\Services\FeedService;
use App\Services\PublicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class PublicationController extends Controller
{
    public function __construct(
        private readonly FeedService $feed,
        private readonly PublicationService $publications,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        // Route publique : on résout tout de même un éventuel jeton Sanctum
        // pour personnaliser la portée géographique du fil.
        $publications = $this->feed->fil(Auth::guard('sanctum')->user(), [
            'scope' => $request->query('scope'),
            // Paramètre `lang` conservé pour compatibilité avec l'ancien client
            // Android : il est accepté mais ignoré (aucun filtre de langue).
            'q' => $request->query('q'),
            'par_page' => (int) $request->query('par_page', 15),
        ]);

        return PublicationResource::collection($publications);
    }

    public function show(Request $request, Publication $publication): PublicationResource
    {
        abort_unless(
            $this->feed->peutVoir(Auth::guard('sanctum')->user(), $publication),
            404,
        );

        $publication->load([
            'utilisateur',
            'category',
            'medias',
            'commentaires.utilisateur',
            'reactions',
        ])->loadCount(['commentaires', 'reactions']);

        return new PublicationResource($publication);
    }

    public function store(EnregistrerPublicationRequest $request): JsonResponse
    {
        $this->authorize('create', Publication::class);

        $publication = $this->publications->creer(
            $request->user(),
            $request->safe()->except(['image', 'images']),
            $this->images($request),
        );

        $publication->load(['utilisateur', 'category', 'medias']);

        return (new PublicationResource($publication))
            ->response()
            ->setStatusCode(201);
    }

    public function update(EnregistrerPublicationRequest $request, Publication $publication): PublicationResource
    {
        $this->authorize('update', $publication);

        $publication = $this->publications->mettreAJour(
            $publication,
            $request->safe()->except(['image', 'images']),
            $this->images($request),
        );

        $publication->load(['utilisateur', 'category', 'medias'])
            ->loadCount(['commentaires', 'reactions']);

        return new PublicationResource($publication);
    }

    public function destroy(Publication $publication): JsonResponse
    {
        $this->authorize('delete', $publication);

        $this->publications->supprimer($publication);

        return response()->json(null, 204);
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function images(Request $request): array
    {
        $images = $request->file('images', []);

        if ($request->file('image') !== null) {
            $images[] = $request->file('image');
        }

        return array_values($images);
    }
}
