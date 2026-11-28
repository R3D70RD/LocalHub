<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SignalementResource;
use App\Models\Signalement;
use App\Services\SignalementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ModerationController extends Controller
{
    public function index(SignalementService $signalements): AnonymousResourceCollection
    {
        return SignalementResource::collection($signalements->file(20));
    }

    public function traiter(Request $request, Signalement $signalement, SignalementService $signalements): JsonResponse
    {
        $donnees = $request->validate([
            'action' => ['required', Rule::in(['rejeter', 'masquer', 'avertir', 'supprimer'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $action = $signalements->traiter(
            $signalement,
            $request->user(),
            $donnees['action'],
            $donnees['note'] ?? null,
        );

        return response()->json([
            'data' => [
                'id' => $action->id_moderation_action,
                'action' => $action->action,
                'id_signalement' => $action->id_signalement,
            ],
        ]);
    }
}
