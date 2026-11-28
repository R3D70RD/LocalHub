<?php

namespace App\Services;

use App\Models\Rencontre;
use App\Models\RencontreMembre;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RencontreService extends ContenuService
{
    /**
     * @param  array{scope?: string|null, q?: string|null, par_page?: int}  $filtres
     */
    public function liste(?User $lecteur, array $filtres = []): LengthAwarePaginator
    {
        $requete = Rencontre::query()
            ->with([
                'utilisateur:id_utilisateur,nom,prenom,avatar_path',
                'categorie:id_categorie,nom,slug',
            ])
            ->withCount(['membres as inscrits_count' => fn ($q) => $q->where('statut', 'inscrit')])
            ->whereIn('statut', ['ouverte', 'complete'])
            ->orderBy('date_heure')
            ->orderBy('id_rencontre');

        return $this->paginer($requete, $lecteur, $filtres);
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    public function creer(User $auteur, array $donnees): Rencontre
    {
        $dateHeure = filled($donnees['date_heure'] ?? null)
            ? Carbon::parse($donnees['date_heure'])->utc()
            : null;

        return Rencontre::create([
            'id_utilisateur' => $auteur->id_utilisateur,
            ...$this->geo($auteur),
            'titre' => $donnees['titre'],
            'description' => $donnees['description'],
            'id_categorie' => $donnees['id_categorie'] ?? null,
            'portee' => $donnees['portee'] ?? $auteur->parametre?->portee_defaut ?? 'quartier',
            'date_heure' => $dateHeure,
            'lieu' => $donnees['lieu'] ?? null,
            'capacite' => $donnees['capacite'] ?? null,
            'interets' => $this->normaliserInterets($donnees['interets'] ?? []),
            'statut' => 'ouverte',
        ]);
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    public function mettreAJour(Rencontre $rencontre, array $donnees): Rencontre
    {
        $valeurs = Arr::only($donnees, [
            'titre', 'description', 'id_categorie', 'portee', 'lieu', 'capacite', 'statut',
        ]);

        if (array_key_exists('date_heure', $donnees)) {
            $valeurs['date_heure'] = filled($donnees['date_heure'])
                ? Carbon::parse($donnees['date_heure'])->utc()
                : null;
        }

        if (array_key_exists('interets', $donnees)) {
            $valeurs['interets'] = $this->normaliserInterets($donnees['interets'] ?? []);
        }

        $nullable = ['id_categorie', 'date_heure', 'lieu', 'capacite'];

        $rencontre->fill(array_filter(
            $valeurs,
            fn ($valeur, $cle) => $valeur !== null || in_array($cle, $nullable, true),
            ARRAY_FILTER_USE_BOTH,
        ));
        $rencontre->save();

        return $rencontre;
    }

    public function supprimer(Rencontre $rencontre): void
    {
        $rencontre->delete();
    }

    public function rejoindre(Rencontre $rencontre, User $utilisateur): RencontreMembre
    {
        $membre = DB::transaction(function () use ($rencontre, $utilisateur) {
            $existante = $rencontre->membres()
                ->where('id_utilisateur', $utilisateur->id_utilisateur)
                ->first();

            if ($existante !== null && $existante->statut === 'inscrit') {
                return $existante;
            }

            if ($rencontre->capacite !== null) {
                $inscrits = $rencontre->membres()->where('statut', 'inscrit')->count();

                if ($inscrits >= $rencontre->capacite) {
                    throw ValidationException::withMessages([
                        'capacite' => __('interface.meetup_full'),
                    ]);
                }
            }

            return $rencontre->membres()->updateOrCreate(
                ['id_utilisateur' => $utilisateur->id_utilisateur],
                ['statut' => 'inscrit'],
            );
        });

        if ($membre->wasRecentlyCreated || $membre->wasChanged('statut')) {
            $organisateur = $rencontre->utilisateur;

            if ($organisateur !== null && (int) $organisateur->id_utilisateur !== (int) $utilisateur->id_utilisateur) {
                $this->notifications->envoyer($organisateur, 'rencontre.membre', [
                    'id_rencontre' => $rencontre->id_rencontre,
                    'titre' => $rencontre->titre,
                ]);
            }
        }

        return $membre;
    }

    public function quitter(Rencontre $rencontre, User $utilisateur): void
    {
        $rencontre->membres()
            ->where('id_utilisateur', $utilisateur->id_utilisateur)
            ->delete();
    }

    /**
     * @param  array<int, mixed>  $interets
     * @return array<int, string>
     */
    private function normaliserInterets(array $interets): array
    {
        return collect($interets)
            ->filter(fn ($interet) => is_string($interet) && trim($interet) !== '')
            ->map(fn (string $interet) => trim($interet))
            ->unique()
            ->take(12)
            ->values()
            ->all();
    }
}
