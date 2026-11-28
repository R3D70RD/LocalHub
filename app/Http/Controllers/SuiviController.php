<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Services\SuiviService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SuiviController extends Controller
{
    public function store(Request $request, Publication $publication, SuiviService $suivis): RedirectResponse
    {
        $suivis->suivre($request->user(), 'publication', $publication->id_publication);

        return back()->with('status', __('interface.following'));
    }

    public function destroy(Request $request, Publication $publication, SuiviService $suivis): RedirectResponse
    {
        $suivis->nePlusSuivre($request->user(), 'publication', $publication->id_publication);

        return back()->with('status', __('interface.not_following'));
    }
}
