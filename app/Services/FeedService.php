<?php

namespace App\Services;

use App\Enums\PorteeVisibilite;
use App\Models\Annonce;
use App\Models\Category;
use App\Models\DemandeAide;
use App\Models\Evenement;
use App\Models\Outil;
use App\Models\Publication;
use App\Models\Rencontre;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public function __construct(private readonly RechercheService $recherche) {}

    /**
     * @param  array{scope?: string, q?: string|null, par_page?: int}  $filtres
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
        $this->recherche->appliquer($requete, $filtres['q'] ?? null);

        return $requete->paginate($parPage)->withQueryString();
    }

    /**
     * Fil unifié de toutes les entités visibles (publié, outils, aide, marché,
     * événements, rencontres), trié par date décroissante.
     *
     * @param  array{scope?: string, q?: string|null, par_page?: int}  $filtres
     */
    public function filUnifie(?User $lecteur, array $filtres = []): LengthAwarePaginator
    {
        $scope = $this->normaliserScope($filtres['scope'] ?? null);
        $parPage = max(1, min(50, (int) ($filtres['par_page'] ?? 10)));
        $recherche = $filtres['q'] ?? null;

        $sources = [
            $this->sourceUnion(
                Publication::query()->where('statut', 'active'),
                'publication', 'id_publication', 'date_creation', 'id_utilisateur',
                $lecteur, $scope, $recherche,
            ),
            $this->sourceUnion(
                Outil::query()->where('statut', 'actif'),
                'outil', 'id_outil', 'created_at', 'id_proprietaire',
                $lecteur, $scope, $recherche,
            ),
            $this->sourceUnion(
                DemandeAide::query()->whereIn('statut', ['ouverte', 'pourvue']),
                'demande_aide', 'id_demande_aide', 'created_at', 'id_utilisateur',
                $lecteur, $scope, $recherche,
            ),
            $this->sourceUnion(
                Annonce::query()->where('statut', 'active'),
                'annonce', 'id_annonce', 'created_at', 'id_utilisateur',
                $lecteur, $scope, $recherche,
            ),
            $this->sourceUnion(
                Evenement::query()->where('statut', 'actif'),
                'evenement', 'id_evenement', 'date_debut', 'id_utilisateur',
                $lecteur, $scope, $recherche,
            ),
            $this->sourceUnion(
                Rencontre::query()->where('statut', 'ouverte'),
                'rencontre', 'id_rencontre', 'COALESCE(date_heure, created_at)', 'id_utilisateur',
                $lecteur, $scope, $recherche,
            ),
        ];

        $union = array_shift($sources)->toBase();

        foreach ($sources as $source) {
            $union->unionAll($source->toBase());
        }

        return DB::query()
            ->fromSub($union, 'fil')
            ->orderByDesc('date_publication')
            ->orderByDesc('id')
            ->paginate($parPage)
            ->withQueryString();
    }

    public function enrichir(LengthAwarePaginator $fil): LengthAwarePaginator
    {
        $items = $fil->getCollection();

        $auteurs = User::query()
            ->whereIn('id_utilisateur', $items->pluck('id_utilisateur')->filter()->unique())
            ->get()
            ->keyBy('id_utilisateur');

        $categories = Category::query()
            ->whereIn('id_categorie', $items->pluck('id_categorie')->filter()->unique())
            ->get()
            ->keyBy('id_categorie');

        $fil->getCollection()->transform(function ($item) use ($auteurs, $categories) {
            $item->auteur = $auteurs->get($item->id_utilisateur);
            $item->categorie = $categories->get($item->id_categorie);

            return $item;
        });

        return $fil;
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
     */
    public function peutVoir(?User $lecteur, Publication $publication): bool
    {
        if ($publication->date_creation === null || $publication->statut !== 'active') {
            return false;
        }

        return $this->peutVoirEntite($lecteur, $publication);
    }

    /**
     * Règle de visibilité générique, réutilisée par toutes les entités de
     * contenu (publication, outil, aide, marché, événement, rencontre).
     */
    public function peutVoirEntite(?User $lecteur, Model $entite): bool
    {
        if ($entite->portee === null) {
            return false;
        }

        if ($lecteur === null) {
            return $entite->portee === PorteeVisibilite::PAYS;
        }

        return match ($entite->portee) {
            PorteeVisibilite::QUARTIER => $lecteur->id_quartier !== null
                && (int) $entite->id_quartier === (int) $lecteur->id_quartier,
            PorteeVisibilite::VILLE => $lecteur->id_ville !== null
                && (int) $entite->id_ville === (int) $lecteur->id_ville,
            PorteeVisibilite::PAYS => $lecteur->ville?->region?->pays?->id_pays !== null
                && (int) $entite->id_pays === (int) $lecteur->ville->region->pays->id_pays,
        };
    }

    public function normaliserScope(?string $scope): string
    {
        return array_key_exists((string) $scope, self::RANGS) ? $scope : 'quartier';
    }

    private function sourceUnion(
        Builder $requete,
        string $type,
        string $cle,
        string $dateExpression,
        string $colonneAuteur,
        ?User $lecteur,
        string $scope,
        ?string $recherche,
    ): Builder {
        $this->appliquerPortee($requete, $lecteur, $scope);
        $this->recherche->appliquer($requete, $recherche);

        return $requete->selectRaw(
            "'{$type}' as type, {$cle} as id, titre, description, {$dateExpression} as date_publication, portee, id_quartier, id_ville, id_pays, {$colonneAuteur} as id_utilisateur, id_categorie"
        );
    }
}
