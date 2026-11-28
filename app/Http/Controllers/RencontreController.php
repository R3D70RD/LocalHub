<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnregistrerRencontreRequest;
use App\Models\Rencontre;
use App\Services\FeedService;
use App\Services\RencontreService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RencontreController extends Controller
{
    public function __construct(
        private readonly RencontreService $rencontres,
        private readonly FeedService $feed,
    ) {}

    public function index(Request $request): View
    {
        $estVisiteur = $request->user() === null;
        $scope = $estVisiteur ? 'pays' : $this->feed->normaliserScope($request->query('scope'));

        $entites = $this->rencontres->liste($request->user(), [
            'scope' => $scope,
            'q' => $request->query('q'),
            'par_page' => 12,
        ]);

        return view('contenus.index', [
            'titre' => __('interface.meetups'),
            'intro' => __('interface.meetups_intro'),
            'routePrefix' => 'rencontres',
            'entites' => $entites,
            'scope' => $scope,
            'estVisiteur' => $estVisiteur,
            'config' => [
                'nouveau' => __('interface.new_meetup'),
                'vide' => __('interface.no_meetups'),
                'badges' => fn (Rencontre $rencontre) => array_values(array_filter([
                    $rencontre->date_heure?->format('Y-m-d H:i'),
                    $rencontre->lieu,
                    $rencontre->interets ? implode(' · ', array_slice($rencontre->interets, 0, 3)) : null,
                ])),
            ],
        ]);
    }

    public function create(): View
    {
        return $this->formulaire(null);
    }

    public function store(EnregistrerRencontreRequest $request): RedirectResponse
    {
        $rencontre = $this->rencontres->creer($request->user(), $request->validated());

        return redirect()->route('rencontres.show', $rencontre)
            ->with('status', __('interface.meetup_created'));
    }

    public function show(Request $request, Rencontre $rencontre): View
    {
        abort_unless($this->feed->peutVoirEntite($request->user(), $rencontre), 404);

        $rencontre->load(['utilisateur', 'categorie', 'membres.utilisateur']);

        $inscrits = $rencontre->membres->where('statut', 'inscrit')->count();
        $membre = $request->user() !== null && $rencontre->membres->contains(
            fn ($membre) => (int) $membre->id_utilisateur === (int) $request->user()->id_utilisateur
                && $membre->statut === 'inscrit',
        );
        $placesRestantes = $rencontre->capacite === null ? null : max(0, $rencontre->capacite - $inscrits);

        return view('rencontres.show', compact('rencontre', 'membre', 'inscrits', 'placesRestantes'));
    }

    public function edit(Rencontre $rencontre): View
    {
        $this->authorize('update', $rencontre);

        return $this->formulaire($rencontre);
    }

    public function update(EnregistrerRencontreRequest $request, Rencontre $rencontre): RedirectResponse
    {
        $this->authorize('update', $rencontre);

        $this->rencontres->mettreAJour($rencontre, $request->validated());

        return redirect()->route('rencontres.show', $rencontre)
            ->with('status', __('interface.meetup_updated'));
    }

    public function destroy(Rencontre $rencontre): RedirectResponse
    {
        $this->authorize('delete', $rencontre);

        $this->rencontres->supprimer($rencontre);

        return redirect()->route('rencontres.index')
            ->with('status', __('interface.meetup_deleted'));
    }

    private function formulaire(?Rencontre $rencontre): View
    {
        return view('contenus.form', [
            'titre' => $rencontre === null ? __('interface.new_meetup') : __('interface.edit'),
            'action' => $rencontre === null
                ? route('rencontres.store')
                : route('rencontres.update', $rencontre),
            'methode' => $rencontre === null ? 'POST' : 'PUT',
            'formPartial' => 'rencontres._form',
            'entite' => $rencontre,
            'retour' => $rencontre === null
                ? route('rencontres.index')
                : route('rencontres.show', $rencontre),
        ]);
    }
}
