<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

/**
 * Recherche plein texte partagée.
 *
 * Sur MySQL, s'appuie sur les index FULLTEXT (`MATCH … AGAINST`). Sous SQLite
 * (tests), replie sur `LIKE` afin de rester portable. La recherche ne porte
 * que sur le texte de l'auteur : titre et description.
 */
class RechercheService
{
    /**
     * @param  array<int, string>  $colonnes
     */
    public function appliquer(Builder $requete, ?string $terme, array $colonnes = ['titre', 'description']): Builder
    {
        $terme = trim((string) $terme);

        if ($terme === '') {
            return $requete;
        }

        if ($requete->getConnection()->getDriverName() === 'mysql') {
            $liste = implode(', ', array_map(fn (string $colonne) => "`{$colonne}`", $colonnes));

            return $requete->whereRaw(
                "MATCH({$liste}) AGAINST (? IN NATURAL LANGUAGE MODE)",
                [$terme],
            );
        }

        $motif = '%'.$terme.'%';

        return $requete->where(function (Builder $q) use ($colonnes, $motif) {
            foreach ($colonnes as $colonne) {
                $q->orWhere($colonne, 'like', $motif);
            }
        });
    }
}
