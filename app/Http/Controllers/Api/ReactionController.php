<?php

namespace App\Http\Controllers\Api;

use App\Enums\TypeReaction;
use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Services\PublicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReactionController extends Controller
{
    public function toggle(Request $request, Publication $publication, PublicationService $publications): JsonResponse
    {
        $donnees = $request->validate([
            'type' => ['required', Rule::in(array_column(TypeReaction::cases(), 'value'))],
        ]);

        $ajoutee = $publications->basculerReaction(
            $publication,
            $request->user(),
            TypeReaction::from($donnees['type']),
        );

        return response()->json([
            'data' => [
                'ajoutee' => $ajoutee,
                'type' => $donnees['type'],
                'total' => $publication->reactions()->count(),
            ],
        ]);
    }
}
