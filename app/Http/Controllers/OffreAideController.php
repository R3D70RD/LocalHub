<?php

namespace App\Http\Controllers;

use App\Models\DemandeAide;
use App\Services\DemandeAideService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OffreAideController extends Controller
{
    public function store(Request $request, DemandeAide $demande, DemandeAideService $demandes): RedirectResponse
    {
        $donnees = $request->validate([
            'titre' => ['nullable', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $demandes->proposerOffre($demande, $request->user(), $donnees);

        return back()->with('status', __('interface.help_offer_sent'));
    }
}
