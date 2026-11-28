<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use App\Services\AnnonceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnonceMessageController extends Controller
{
    public function store(Request $request, Annonce $annonce, AnnonceService $annonces): RedirectResponse
    {
        $donnees = $request->validate([
            'contenu' => ['required', 'string', 'max:2000'],
        ]);

        $annonces->envoyerMessage($annonce, $request->user(), $donnees['contenu']);

        return back()->with('status', __('interface.message_sent'));
    }
}
