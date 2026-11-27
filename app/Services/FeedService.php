<?php

namespace App\Services;

use App\Enums\PorteeVisibilite;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Construit le fil géolocalisé.
 *
 * Règle de visibilité : la portée choisie par l'auteur est un plafond ; la
 * portée demandée par le lecteur (`scope`) est un rayon de recherche. Les deux
 * doivent concorder, et l'identifiant géographique au niveau concerné doit
 * correspondre.
 */
class FeedService
{
    private const RANGS = [
        'quartier' => 1,
        'ville' => 2,
        'pays' => 3,
    ];

    /**
     * @param  array{scope?: string, lang?: string|null, q?: string|null, par_page?: int}  $filtres
     */
    public function fil(?User $lecteur, array $filtres = []): LengthAwarePaginator
    {
        $scope = $this->normaliserScope($filtres['scope'] ?? null);
        $parPage = max(1, min(50, (int) ($filtres['par_page'] ?? 10)));

        $requete = Publication::query()
            ->with([
                'utilisateur:id_utilisateur,nom,prenom,avatar_path',
                'category:id_categorie,nom,slug',
                'medias',
            ])
            ->withCount(['reactions', 'commentaires'])
            ->where('statut', 'active')
            ->orderByDesc('date_creation')
            ->orderByDesc('id_publication');

        $this->appliquerPortee($requete, $lecteur, $scope);

        if (! empty($filtres['lang']) && in_array($filtres['lang'], ['fr', 'en'], true)) {
            $requete->where('lang', $filtres['lang']);
        }

        if (! empty($filtres['q'])) {
            $recherche = '%'.$filtres['q'].'%';
            $requete->where(function (Builder $q) use ($recherche) {
                $q->where('titre', 'like', $recherche)
                    ->orWhere('description', 'like', $recherche);
            });
        }

        return $requete->paginate($parPage)->withQueryString();
    }

    public function appliquerPortee(Builder $requete, ?User $lecteur, string $scope): Builder
    {
        if ($lecteur === null) {
            // Les visiteurs non connectés ne voient que le contenu public à
            // l'échelle du pays, jamais les publications de quartier.
            return $requete->where('portee', PorteeVisibilite::PAYS->value);
        }

        $rang = self::RANGS[$scope] ?? 1;
        $idPays = $lecteur->ville?->region?->pays?->id_pays;

        // Un utilisateur qui n'a pas choisi de quartier (inscription « ville
        // seulement ») doit tout de même voir le contenu de sa ville.
        if ($rang >= 1 && $lecteur->id_quartier === null && $lecteur->id_ville !== null) {
            $rang = max($rang, 2);
        }

        if ($rang >= 2 && $lecteur->id_ville === null && $idPays !== null) {
            $rang = max($rang, 3);
        }

        $auMoinsUneCondition = false;

        $requete->where(function (Builder $q) use ($lecteur, $rang, $idPays, &$auMoinsUneCondition) {
            if ($rang >= 1 && $lecteur->id_quartier !== null) {
                $q->orWhere(function (Builder $qq) use ($lecteur) {
                    $qq->where('portee', PorteeVisibilite::QUARTIER->value)
                        ->where('id_quartier', $lecteur->id_quartier);
                });
                $auMoinsUneCondition = true;
            }

            if ($rang >= 2 && $lecteur->id_ville !== null) {
                $q->orWhere(function (Builder $qq) use ($lecteur) {
                    $qq->where('portee', PorteeVisibilite::VILLE->value)
                        ->where('id_ville', $lecteur->id_ville);
                });
                $auMoinsUneCondition = true;
            }

            if ($rang >= 3 && $idPays !== null) {
                $q->orWhere(function (Builder $qq) use ($idPays) {
                    $qq->where('portee', PorteeVisibilite::PAYS->value)
                        ->where('id_pays', $idPays);
                });
                $auMoinsUneCondition = true;
            }
        });

        if (! $auMoinsUneCondition) {
            $requete->whereRaw('1 = 0');
        }

        return $requete;
    }

    /**
     * Indique si un lecteur peut consulter une publication donnée.
     *
     * Les visiteurs non connectés n'ont accès qu'aux publications à l'échelle
     * du pays. Un lecteur connecté doit partager le niveau géographique requis
     * par la portée de la publication (quartier, ville ou pays).
     */
    public function peutVoir(?User $lecteur, Publication $publication): bool
    {
        if ($publication->date_creation === null || $publication->statut !== 'active' || $publication->portee === null) {
            return false;
        }

        if ($lecteur === null) {
            return $publication->portee === PorteeVisibilite::PAYS;
        }

        return match ($publication->portee) {
            PorteeVisibilite::QUARTIER => $lecteur->id_quartier !== null
                && $publication->id_quartier === $lecteur->id_quartier,
            PorteeVisibilite::VILLE => $lecteur->id_ville !== null
                && $publication->id_ville === $lecteur->id_ville,
            PorteeVisibilite::PAYS => $lecteur->ville?->region?->pays?->id_pays !== null
                && $publication->id_pays === $lecteur->ville->region->pays->id_pays,
        };
    }

    public function normaliserScope(?string $scope): string
    {
        return array_key_exists((string) $scope, self::RANGS) ? $scope : 'quartier';
    }
}
