<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request, ConversationService $service): View
    {
        $utilisateur = $request->user();
        $conversations = $service->listePour($utilisateur);
        $nonLus = $conversations->mapWithKeys(
            fn (Conversation $conversation) => [$conversation->id_conversation => $service->nonLus($conversation, $utilisateur)],
        );
        $destinataires = User::query()
            ->where('id_utilisateur', '!=', $utilisateur->id_utilisateur)
            ->orderBy('prenom')
            ->get();

        return view('conversations.index', compact('conversations', 'nonLus', 'destinataires'));
    }

    public function demarrer(Request $request, ConversationService $service): RedirectResponse
    {
        $donnees = $request->validate([
            'destinataire' => ['required', 'integer', 'exists:utilisateur,id_utilisateur'],
        ]);

        $destinataire = User::query()->findOrFail($donnees['destinataire']);
        $conversation = $service->demarrer($request->user(), $destinataire);

        return redirect()->route('conversations.show', $conversation);
    }

    public function show(Request $request, Conversation $conversation, ConversationService $service): View
    {
        abort_unless($conversation->membreDe($request->user()->id_utilisateur) !== null, 403);

        $service->marquerLu($conversation, $request->user());
        $conversation->load(['membres.utilisateur', 'messages.expediteur']);

        return view('conversations.show', compact('conversation'));
    }

    public function store(Request $request, Conversation $conversation, ConversationService $service): RedirectResponse
    {
        abort_unless($conversation->membreDe($request->user()->id_utilisateur) !== null, 403);

        $donnees = $request->validate([
            'contenu' => ['required', 'string', 'max:2000'],
        ]);

        $service->envoyer($conversation, $request->user(), $donnees['contenu']);

        return back()->with('status', __('interface.message_sent'));
    }
}
