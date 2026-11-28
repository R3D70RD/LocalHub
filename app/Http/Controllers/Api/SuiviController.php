<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Services\SuiviService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuiviController extends Controller
{
    public function store(Request $request, Publication $publication, SuiviService $suivis): JsonResponse
    {
        $suivis->suivre($request->user(), 'publication', $publication->id_publication);

        return response()->json(['suivi' => true], 201);
    }

    public function destroy(Request $request, Publication $publication, SuiviService $suivis): JsonResponse
    {
        $suivis->nePlusSuivre($request->user(), 'publication', $publication->id_publication);

        return response()->json(null, 204);
    }
}
