<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConversationController extends Controller
{
    public function index(Request $request, ConversationService $service): AnonymousResourceCollection
    {
        return ConversationResource::collection($service->listePour($request->user()));
    }

    public function show(Request $request, Conversation $conversation, ConversationService $service): ConversationResource
    {
        abort_unless($conversation->membreDe($request->user()->id_utilisateur) !== null, 403);

        $service->marquerLu($conversation, $request->user());
        $conversation->load(['membres.utilisateur', 'messages.expediteur']);

        return new ConversationResource($conversation);
    }

    public function demarrer(Request $request, ConversationService $service): JsonResponse
    {
        $donnees = $request->validate([
            'destinataire' => ['required', 'integer', 'exists:utilisateur,id_utilisateur'],
        ]);

        $destinataire = User::query()->findOrFail($donnees['destinataire']);
        $conversation = $service->demarrer($request->user(), $destinataire);
        $conversation->load(['membres.utilisateur', 'messages.expediteur']);

        return (new ConversationResource($conversation))
            ->response()
            ->setStatusCode(201);
    }

    public function store(Request $request, Conversation $conversation, ConversationService $service): JsonResponse
    {
        abort_unless($conversation->membreDe($request->user()->id_utilisateur) !== null, 403);

        $donnees = $request->validate([
            'contenu' => ['required', 'string', 'max:2000'],
        ]);

        $message = $service->envoyer($conversation, $request->user(), $donnees['contenu']);
        $message->load('expediteur');

        return (new MessageResource($message))
            ->response()
            ->setStatusCode(201);
    }
}
