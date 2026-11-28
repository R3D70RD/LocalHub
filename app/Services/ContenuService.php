<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Base commune aux entités de contenu de la phase 2 (aide, marché, événements,
 * rencontres). Mutualise le périmétrage géographique et la recherche.
 */
abstract class ContenuService
{
    public function __construct(
        protected readonly FeedService $feed,
        protected readonly RechercheService $recherche,
        protected readonly NotificationService $notifications,
        protected readonly BlocageService $blocages,
    ) {}

    /**
     * @param  array{scope?: string|null, q?: string|null, par_page?: int}  $filtres
     */
    protected function paginer(Builder $requete, ?User $lecteur, array $filtres, int $parPageDefaut = 12): LengthAwarePaginator
    {
        $scope = $this->feed->normaliserScope($filtres['scope'] ?? null);
        $parPage = max(1, min(50, (int) ($filtres['par_page'] ?? $parPageDefaut)));

        $this->feed->appliquerPortee($requete, $lecteur, $scope);
        $this->recherche->appliquer($requete, $filtres['q'] ?? null);
        $this->blocages->exclure($requete, $lecteur);

        return $requete->paginate($parPage)->withQueryString();
    }

    /**
     * @return array{id_quartier: int|null, id_ville: int|null, id_pays: int|null}
     */
    protected function geo(User $auteur): array
    {
        return [
            'id_quartier' => $auteur->id_quartier,
            'id_ville' => $auteur->id_ville,
            'id_pays' => $auteur->ville?->region?->pays?->id_pays,
        ];
    }
}
