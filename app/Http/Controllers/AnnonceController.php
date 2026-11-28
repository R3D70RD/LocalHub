<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnregistrerAnnonceRequest;
use App\Models\Annonce;
use App\Services\AnnonceService;
use App\Services\FeedService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnonceController extends Controller
{
    public function __construct(
        private readonly AnnonceService $annonces,
        private readonly FeedService $feed,
    ) {}

    public function index(Request $request): View
    {
        $estVisiteur = $request->user() === null;
        $scope = $estVisiteur ? 'pays' : $this->feed->normaliserScope($request->query('scope'));

        $entites = $this->annonces->liste($request->user(), [
            'scope' => $scope,
            'q' => $request->query('q'),
            'par_page' => 12,
        ]);

        return view('contenus.index', [
            'titre' => __('interface.market'),
            'intro' => __('interface.market_intro'),
            'routePrefix' => 'annonces',
            'entites' => $entites,
            'scope' => $scope,
            'estVisiteur' => $estVisiteur,
            'config' => [
                'nouveau' => __('interface.new_annonce'),
                'vide' => __('interface.no_annonces'),
                'badges' => fn (Annonce $annonce) => array_values(array_filter([
                    $annonce->estGratuite()
                        ? __('interface.free')
                        : ($annonce->prix !== null ? $annonce->prix.' $' : null),
                    __('interface.type_'.$annonce->type_annonce),
                ])),
            ],
        ]);
    }

    public function create(): View
    {
        return $this->formulaire(null);
    }

    public function store(EnregistrerAnnonceRequest $request): RedirectResponse
    {
        $annonce = $this->annonces->creer($request->user(), $request->validated());

        return redirect()->route('annonces.show', $annonce)
            ->with('status', __('interface.annonce_created'));
    }

    public function show(Request $request, Annonce $annonce): View
    {
        abort_unless($this->feed->peutVoirEntite($request->user(), $annonce), 404);

        $annonce->load(['utilisateur', 'categorie', 'offres.utilisateur', 'messages.expediteur']);

        return view('annonces.show', compact('annonce'));
    }

    public function edit(Annonce $annonce): View
    {
        $this->authorize('update', $annonce);

        return $this->formulaire($annonce);
    }

    public function update(EnregistrerAnnonceRequest $request, Annonce $annonce): RedirectResponse
    {
        $this->authorize('update', $annonce);

        $this->annonces->mettreAJour($annonce, $request->validated());

        return redirect()->route('annonces.show', $annonce)
            ->with('status', __('interface.annonce_updated'));
    }

    public function destroy(Annonce $annonce): RedirectResponse
    {
        $this->authorize('delete', $annonce);

        $this->annonces->supprimer($annonce);

        return redirect()->route('annonces.index')
            ->with('status', __('interface.annonce_deleted'));
    }

    private function formulaire(?Annonce $annonce): View
    {
        return view('contenus.form', [
            'titre' => $annonce === null ? __('interface.new_annonce') : __('interface.edit'),
            'action' => $annonce === null
                ? route('annonces.store')
                : route('annonces.update', $annonce),
            'methode' => $annonce === null ? 'POST' : 'PUT',
            'formPartial' => 'annonces._form',
            'entite' => $annonce,
            'retour' => $annonce === null
                ? route('annonces.index')
                : route('annonces.show', $annonce),
        ]);
    }
}
