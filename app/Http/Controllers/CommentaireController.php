<?php

namespace App\Http\Controllers;

use App\Enums\TypeReaction;
use App\Models\Publication;
use App\Services\PublicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentaireController extends Controller
{
    public function store(Request $request, Publication $publication, PublicationService $publications): RedirectResponse
    {
        $donnees = $request->validate([
            'contenu' => ['required', 'string', 'max:2000'],
        ]);

        $publications->commenter(
            $publication,
            $request->user(),
            $donnees['contenu'],
        );

        return back()->with('status', __('interface.comment_created'));
    }

    public function toggleReaction(Request $request, Publication $publication, PublicationService $publications): RedirectResponse
    {
        $donnees = $request->validate([
            'type' => ['required', Rule::in(array_column(TypeReaction::cases(), 'value'))],
        ]);

        $ajoutee = $publications->basculerReaction(
            $publication,
            $request->user(),
            TypeReaction::from($donnees['type']),
        );

        return back()->with('status', $ajoutee
            ? __('interface.reaction_added')
            : __('interface.reaction_removed'));
    }
}
