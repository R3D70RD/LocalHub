<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BlocageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlocageController extends Controller
{
    public function store(Request $request, User $utilisateur, BlocageService $blocages): JsonResponse
    {
        abort_if((int) $request->user()->id_utilisateur === (int) $utilisateur->id_utilisateur, 422);

        $blocages->bloquer($request->user(), $utilisateur);

        return response()->json(null, 204);
    }

    public function destroy(Request $request, User $utilisateur, BlocageService $blocages): JsonResponse
    {
        $blocages->debloquer($request->user(), $utilisateur);

        return response()->json(null, 204);
    }
}
