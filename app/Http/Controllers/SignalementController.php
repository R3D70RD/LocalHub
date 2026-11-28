<?php

namespace App\Http\Controllers;

use App\Services\SignalementService;
use App\Support\Cibles;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SignalementController extends Controller
{
    public function store(Request $request, SignalementService $signalements): RedirectResponse
    {
        $donnees = $request->validate([
            'cible_type' => ['required', Rule::in(Cibles::cles())],
            'cible_id' => ['required', 'integer'],
            'motif' => ['required', Rule::in(['spam', 'harcelement', 'contenu_illegal', 'autre'])],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $classe = Cibles::classe($donnees['cible_type']);
        $cible = $classe::query()->find($donnees['cible_id']);

        abort_if($cible === null, 404);

        $signalements->signaler(
            $request->user(),
            $donnees['cible_type'],
            (int) $donnees['cible_id'],
            $donnees['motif'],
            $donnees['description'] ?? null,
        );

        return back()->with('status', __('interface.report_sent'));
    }
}
