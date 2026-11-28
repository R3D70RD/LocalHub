<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use App\Services\AnnonceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OffreAnnonceController extends Controller
{
    public function store(Request $request, Annonce $annonce, AnnonceService $annonces): RedirectResponse
    {
        $donnees = $request->validate([
            'montant' => ['nullable', 'numeric', 'min:0'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $annonces->proposerOffre($annonce, $request->user(), $donnees);

        return back()->with('status', __('interface.offer_sent'));
    }
}
