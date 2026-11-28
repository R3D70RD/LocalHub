<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnregistrerOutilRequest;
use App\Http\Resources\OutilResource;
use App\Models\Outil;
use App\Services\OutilService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class OutilController extends Controller
{
    public function __construct(private readonly OutilService $outils) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $outils = $this->outils->liste(Auth::guard('sanctum')->user(), [
            'scope' => $request->query('scope'),
            'q' => $request->query('q'),
            'par_page' => (int) $request->query('par_page', 15),
        ]);

        return OutilResource::collection($outils);
    }

    public function show(Outil $outil): OutilResource
    {
        $outil->load(['proprietaire', 'categorie', 'medias']);

        return new OutilResource($outil);
    }

    public function store(EnregistrerOutilRequest $request): JsonResponse
    {
        $this->authorize('create', Outil::class);

        $outil = $this->outils->creer(
            $request->user(),
            $request->safe()->except('images'),
            $request->file('images', []),
        );

        $outil->load(['proprietaire', 'categorie', 'medias']);

        return (new OutilResource($outil))->response()->setStatusCode(201);
    }

    public function update(EnregistrerOutilRequest $request, Outil $outil): OutilResource
    {
        $this->authorize('update', $outil);

        $outil = $this->outils->mettreAJour(
            $outil,
            $request->safe()->except('images'),
            $request->file('images', []),
        );

        $outil->load(['proprietaire', 'categorie', 'medias']);

        return new OutilResource($outil);
    }

    public function destroy(Outil $outil): JsonResponse
    {
        $this->authorize('delete', $outil);

        $this->outils->supprimer($outil);

        return response()->json(null, 204);
    }
}
