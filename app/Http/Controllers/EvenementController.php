<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnregistrerEvenementRequest;
use App\Models\Evenement;
use App\Services\EvenementService;
use App\Services\FeedService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EvenementController extends Controller
{
    public function __construct(
        private readonly EvenementService $evenements,
        private readonly FeedService $feed,
    ) {}

    public function index(Request $request): View
    {
        $estVisiteur = $request->user() === null;
        $scope = $estVisiteur ? 'pays' : $this->feed->normaliserScope($request->query('scope'));

        $entites = $this->evenements->liste($request->user(), [
            'scope' => $scope,
            'q' => $request->query('q'),
            'par_page' => 12,
        ]);

        return view('contenus.index', [
            'titre' => __('interface.events'),
            'intro' => __('interface.events_intro'),
            'routePrefix' => 'evenements',
            'entites' => $entites,
            'scope' => $scope,
            'estVisiteur' => $estVisiteur,
            'config' => [
                'nouveau' => __('interface.new_event'),
                'vide' => __('interface.no_events'),
                'badges' => fn (Evenement $evenement) => array_values(array_filter([
                    $evenement->date_debut?->copy()->setTimezone($evenement->timezone_id)->format('Y-m-d H:i'),
                    $evenement->lieu,
                ])),
            ],
        ]);
    }

    public function create(): View
    {
        return $this->formulaire(null);
    }

    public function store(EnregistrerEvenementRequest $request): RedirectResponse
    {
        $evenement = $this->evenements->creer($request->user(), $request->validated());

        return redirect()->route('evenements.show', $evenement)
            ->with('status', __('interface.event_created'));
    }

    public function show(Request $request, Evenement $evenement): View
    {
        abort_unless($this->feed->peutVoirEntite($request->user(), $evenement), 404);

        $evenement->load(['utilisateur', 'categorie', 'inscriptions.utilisateur']);

        $inscrits = $evenement->inscriptions->where('statut', 'inscrit')->count();
        $inscrit = $request->user() !== null && $evenement->inscriptions->contains(
            fn ($inscription) => (int) $inscription->id_utilisateur === (int) $request->user()->id_utilisateur
                && $inscription->statut === 'inscrit',
        );
        $placesRestantes = $evenement->capacite === null ? null : max(0, $evenement->capacite - $inscrits);

        return view('evenements.show', compact('evenement', 'inscrit', 'inscrits', 'placesRestantes'));
    }

    public function edit(Evenement $evenement): View
    {
        $this->authorize('update', $evenement);

        return $this->formulaire($evenement);
    }

    public function update(EnregistrerEvenementRequest $request, Evenement $evenement): RedirectResponse
    {
        $this->authorize('update', $evenement);

        $this->evenements->mettreAJour($evenement, $request->validated());

        return redirect()->route('evenements.show', $evenement)
            ->with('status', __('interface.event_updated'));
    }

    public function destroy(Evenement $evenement): RedirectResponse
    {
        $this->authorize('delete', $evenement);

        $this->evenements->supprimer($evenement);

        return redirect()->route('evenements.index')
            ->with('status', __('interface.event_deleted'));
    }

    private function formulaire(?Evenement $evenement): View
    {
        return view('contenus.form', [
            'titre' => $evenement === null ? __('interface.new_event') : __('interface.edit'),
            'action' => $evenement === null
                ? route('evenements.store')
                : route('evenements.update', $evenement),
            'methode' => $evenement === null ? 'POST' : 'PUT',
            'formPartial' => 'evenements._form',
            'entite' => $evenement,
            'retour' => $evenement === null
                ? route('evenements.index')
                : route('evenements.show', $evenement),
        ]);
    }
}
