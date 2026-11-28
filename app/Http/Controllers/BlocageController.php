<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BlocageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlocageController extends Controller
{
    public function store(Request $request, User $utilisateur, BlocageService $blocages): RedirectResponse
    {
        abort_if((int) $request->user()->id_utilisateur === (int) $utilisateur->id_utilisateur, 422);

        $blocages->bloquer($request->user(), $utilisateur);

        return back()->with('status', __('interface.user_blocked'));
    }

    public function destroy(Request $request, User $utilisateur, BlocageService $blocages): RedirectResponse
    {
        $blocages->debloquer($request->user(), $utilisateur);

        return back()->with('status', __('interface.user_unblocked'));
    }
}
