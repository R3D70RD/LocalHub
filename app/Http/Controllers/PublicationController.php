<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnregistrerPublicationRequest;
use App\Models\Publication;
use App\Services\FeedService;
use App\Services\PublicationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class PublicationController extends Controller
{
    public function __construct(
        private readonly FeedService $feed,
        private readonly PublicationService $publications,
    ) {}

    public function index(Request $request): View
    {
        $publications = $this->feed->fil($request->user(), [
            'scope' => $request->query('scope'),
            'lang' => $request->query('lang'),
            'q' => $request->query('q'),
            'par_page' => 10,
        ]);

        return view('publications.index', [
            'publications' => $publications,
            'scope' => $this->feed->normaliserScope($request->query('scope')),
            'lang' => $request->query('lang'),
        ]);
    }

    public function create(): View
    {
        return view('publications.create');
    }

    public function store(EnregistrerPublicationRequest $request): RedirectResponse
    {
        $publication = $this->publications->creer(
            $request->user(),
            $request->safe()->except(['image', 'images']),
            $this->images($request),
        );

        return redirect()->route('publications.show', $publication)
            ->with('status', __('interface.publication_created'));
    }

    public function show(Publication $publication): View
    {
        abort_if($publication->date_creation === null, 404);

        $publication->load([
            'utilisateur',
            'category',
            'medias',
            'commentaires.utilisateur',
            'reactions',
        ]);

        $compteursReactions = $publication->reactions
            ->groupBy(fn ($reaction) => $reaction->type->value)
            ->map->count();

        $reactionsUtilisateur = auth()->check()
            ? $publication->reactions
                ->where('id_utilisateur', auth()->id())
                ->pluck('type')
                ->map(fn ($type) => $type->value)
                ->all()
            : [];

        return view('publications.show', [
            'publication' => $publication,
            'compteursReactions' => $compteursReactions,
            'reactionsUtilisateur' => $reactionsUtilisateur,
        ]);
    }

    public function edit(Publication $publication): View
    {
        $this->authorize('update', $publication);

        return view('publications.edit', compact('publication'));
    }

    public function update(EnregistrerPublicationRequest $request, Publication $publication): RedirectResponse
    {
        $this->authorize('update', $publication);

        $this->publications->mettreAJour(
            $publication,
            $request->safe()->except(['image', 'images']),
            $this->images($request),
        );

        return redirect()->route('publications.show', $publication)
            ->with('status', __('interface.publication_updated'));
    }

    public function destroy(Publication $publication): RedirectResponse
    {
        $this->authorize('delete', $publication);

        $this->publications->supprimer($publication);

        return redirect()->route('publications.index')
            ->with('status', __('interface.publication_deleted'));
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function images(Request $request): array
    {
        $images = $request->file('images', []);

        if ($request->file('image') !== null) {
            $images[] = $request->file('image');
        }

        return array_values($images);
    }
}
