<?php

namespace App\Http\Controllers;

use App\Models\Rencontre;
use App\Services\RencontreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RencontreMembreController extends Controller
{
    public function store(Request $request, Rencontre $rencontre, RencontreService $rencontres): RedirectResponse
    {
        $rencontres->rejoindre($rencontre, $request->user());

        return back()->with('status', __('interface.meetup_joined'));
    }

    public function destroy(Request $request, Rencontre $rencontre, RencontreService $rencontres): RedirectResponse
    {
        $rencontres->quitter($rencontre, $request->user());

        return back()->with('status', __('interface.meetup_left'));
    }
}
