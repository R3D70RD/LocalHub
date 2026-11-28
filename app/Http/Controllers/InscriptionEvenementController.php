<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Services\EvenementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InscriptionEvenementController extends Controller
{
    public function store(Request $request, Evenement $evenement, EvenementService $evenements): RedirectResponse
    {
        $evenements->inscrire($evenement, $request->user());

        return back()->with('status', __('interface.registered'));
    }

    public function destroy(Request $request, Evenement $evenement, EvenementService $evenements): RedirectResponse
    {
        $evenements->annulerInscription($evenement, $request->user());

        return back()->with('status', __('interface.unregistered'));
    }
}
