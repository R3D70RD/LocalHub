<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecherchePublicationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:50'],
        ]);

        $publications = Publication::query()
            ->whereNotNull('date_creation')
            ->where('titre', 'like', '%'.$data['q'].'%')
            ->orderBy('titre')
            ->limit(8)
            ->get(['id', 'titre']);

        return response()->json([
            'data' => $publications->map(fn (Publication $publications) => [
                'id' => $publications->id,
                'label' => $publication->titre,
                'url' => route('publications.show', $publications),
            ]),
        ]);
    }
}
