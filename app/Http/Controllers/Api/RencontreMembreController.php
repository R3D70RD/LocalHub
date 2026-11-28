<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RencontreMembreResource;
use App\Models\Rencontre;
use App\Services\RencontreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RencontreMembreController extends Controller
{
    public function store(Request $request, Rencontre $rencontre, RencontreService $rencontres): JsonResponse
    {
        $membre = $rencontres->rejoindre($rencontre, $request->user());
        $membre->load('utilisateur');

        return (new RencontreMembreResource($membre))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request, Rencontre $rencontre, RencontreService $rencontres): JsonResponse
    {
        $rencontres->quitter($rencontre, $request->user());

        return response()->json(null, 204);
    }
}
