<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnregistrerDemandeAideRequest;
use App\Models\DemandeAide;
use App\Services\DemandeAideService;
use App\Services\FeedService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DemandeAideController extends Controller
{
    public function __construct(
        private readonly DemandeAideService $demandes,
        private readonly FeedService $feed,
    ) {}

    public function index(Request $request): View
    {
        $estVisiteur = $request->user() === null;
        $scope = $estVisiteur ? 'pays' : $this->feed->normaliserScope($request->query('scope'));

        $entites = $this->demandes->liste($request->user(), [
            'scope' => $scope,
            'q' => $request->query('q'),
            'par_page' => 12,
        ]);

        return view('contenus.index', [
            'titre' => __('interface.help_requests'),
            'intro' => __('interface.help_requests_intro'),
            'routePrefix' => 'demandes-aide',
            'entites' => $entites,
            'scope' => $scope,
            'estVisiteur' => $estVisiteur,
            'config' => [
                'nouveau' => __('interface.new_help_request'),
                'vide' => __('interface.no_help_requests'),
                'badges' => fn (DemandeAide $demande) => array_values(array_filter([
                    $demande->urgent ? __('interface.urgent') : null,
                    $demande->date_limite ? __('interface.deadline').' : '.$demande->date_limite->toDateString() : null,
                ])),
            ],
        ]);
    }

    public function create(): View
    {
        return $this->formulaire(null);
    }

    public function store(EnregistrerDemandeAideRequest $request): RedirectResponse
    {
        $demande = $this->demandes->creer($request->user(), $request->validated());

        return redirect()->route('demandes-aide.show', $demande)
            ->with('status', __('interface.help_request_created'));
    }

    public function show(Request $request, DemandeAide $demande): View
    {
        abort_unless($this->feed->peutVoirEntite($request->user(), $demande), 404);

        $demande->load(['utilisateur', 'categorie', 'offres.utilisateur']);

        $dejaOffert = $request->user() !== null
            && $demande->offres->contains('id_utilisateur', $request->user()->id_utilisateur);

        return view('demandes-aide.show', compact('demande', 'dejaOffert'));
    }

    public function edit(DemandeAide $demande): View
    {
        $this->authorize('update', $demande);

        return $this->formulaire($demande);
    }

    public function update(EnregistrerDemandeAideRequest $request, DemandeAide $demande): RedirectResponse
    {
        $this->authorize('update', $demande);

        $this->demandes->mettreAJour($demande, $request->validated());

        return redirect()->route('demandes-aide.show', $demande)
            ->with('status', __('interface.help_request_updated'));
    }

    public function destroy(DemandeAide $demande): RedirectResponse
    {
        $this->authorize('delete', $demande);

        $this->demandes->supprimer($demande);

        return redirect()->route('demandes-aide.index')
            ->with('status', __('interface.help_request_deleted'));
    }

    private function formulaire(?DemandeAide $demande): View
    {
        return view('contenus.form', [
            'titre' => $demande === null ? __('interface.new_help_request') : __('interface.edit'),
            'action' => $demande === null
                ? route('demandes-aide.store')
                : route('demandes-aide.update', $demande),
            'methode' => $demande === null ? 'POST' : 'PUT',
            'formPartial' => 'demandes-aide._form',
            'entite' => $demande,
            'retour' => $demande === null
                ? route('demandes-aide.index')
                : route('demandes-aide.show', $demande),
        ]);
    }
}
