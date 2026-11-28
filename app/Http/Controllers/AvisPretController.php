<?php

namespace App\Http\Controllers;

use App\Exceptions\ActionNonAutoriseeException;
use App\Exceptions\TransitionInvalideException;
use App\Models\Pret;
use App\Services\AvisPretService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AvisPretController extends Controller
{
    public function store(Request $request, Pret $pret, AvisPretService $avis): RedirectResponse
    {
        $donnees = $request->validate([
            'note' => ['required', 'integer', 'min:1', 'max:5'],
            'commentaire' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $avis->noter($pret, $request->user(), (int) $donnees['note'], $donnees['commentaire'] ?? null);
        } catch (ActionNonAutoriseeException|TransitionInvalideException $exception) {
            return back()->withErrors(['note' => $exception->getMessage()]);
        }

        return back()->with('status', __('interface.review_saved'));
    }
}
