<?php

namespace App\Http\Controllers;

use App\Models\Signalement;
use App\Services\SignalementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModerationController extends Controller
{
    public function index(SignalementService $signalements): View
    {
        return view('moderation.index', [
            'signalements' => $signalements->file(20),
        ]);
    }

    public function traiter(Request $request, Signalement $signalement, SignalementService $signalements): RedirectResponse
    {
        $donnees = $request->validate([
            'action' => ['required', Rule::in(['rejeter', 'masquer', 'avertir', 'supprimer'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $signalements->traiter(
            $signalement,
            $request->user(),
            $donnees['action'],
            $donnees['note'] ?? null,
        );

        return back()->with('status', __('interface.report_handled'));
    }
}
