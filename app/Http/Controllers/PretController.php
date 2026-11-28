<?php

namespace App\Http\Controllers;

use App\Enums\StatutPret;
use App\Exceptions\ActionNonAutoriseeException;
use App\Exceptions\ConflitPretException;
use App\Exceptions\TransitionInvalideException;
use App\Http\Requests\DemanderPretRequest;
use App\Http\Requests\TransitionPretRequest;
use App\Models\Outil;
use App\Models\Pret;
use App\Services\LoanService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PretController extends Controller
{
    public function __construct(private readonly LoanService $prets) {}

    public function index(Request $request): View
    {
        $prets = Pret::query()
            ->pourUtilisateur($request->user()->id_utilisateur)
            ->with(['outil', 'proprietaire', 'emprunteur'])
            ->orderByDesc('id_pret')
            ->paginate(10);

        return view('prets.index', compact('prets'));
    }

    public function store(DemanderPretRequest $request, Outil $outil): RedirectResponse
    {
        try {
            $pret = $this->prets->demander(
                $request->user(),
                $outil,
                $request->validated(),
            );
        } catch (ConflitPretException|TransitionInvalideException $exception) {
            return back()->withErrors(['pret' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('prets.show', $pret)
            ->with('status', __('interface.loan_created'));
    }

    public function show(Pret $pret): View
    {
        $this->prets->verifierParticipant($pret, request()->user());

        $pret->load([
            'outil.medias',
            'proprietaire',
            'emprunteur',
            'messages.expediteur',
        ]);

        return view('prets.show', [
            'pret' => $pret,
            'actions' => $this->prets->actionsAutorisees($pret, request()->user()),
            'peutNoter' => $pret->statut === StatutPret::RETOURNE,
            'avisExistant' => $pret->avis()
                ->where('id_auteur', request()->user()->id_utilisateur)
                ->exists(),
        ]);
    }

    public function transition(TransitionPretRequest $request, Pret $pret): RedirectResponse
    {
        try {
            $this->prets->transition(
                $pret,
                $request->validated('action'),
                $request->user(),
                $request->validated('version'),
            );
        } catch (ActionNonAutoriseeException $exception) {
            return back()->withErrors(['action' => $exception->getMessage()]);
        } catch (ConflitPretException|TransitionInvalideException $exception) {
            return back()->withErrors(['action' => $exception->getMessage()]);
        }

        return back()->with('status', __('interface.loan_transition_done'));
    }

    public function storeMessage(Request $request, Pret $pret): RedirectResponse
    {
        $donnees = $request->validate([
            'contenu' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $this->prets->envoyerMessage(
                $pret,
                $request->user(),
                $donnees['contenu'],
            );
        } catch (ActionNonAutoriseeException $exception) {
            return back()->withErrors(['contenu' => $exception->getMessage()]);
        }

        return back()->with('status', __('interface.message_sent'));
    }
}
