<?php

namespace App\Services;

use App\Models\Evenement;
use App\Models\InscriptionEvenement;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EvenementService extends ContenuService
{
    /**
     * @param  array{scope?: string|null, q?: string|null, par_page?: int}  $filtres
     */
    public function liste(?User $lecteur, array $filtres = []): LengthAwarePaginator
    {
        $requete = Evenement::query()
            ->with([
                'utilisateur:id_utilisateur,nom,prenom,avatar_path',
                'categorie:id_categorie,nom,slug',
            ])
            ->withCount(['inscriptions as inscrits_count' => fn ($q) => $q->where('statut', 'inscrit')])
            ->where('statut', 'actif')
            ->orderBy('date_debut')
            ->orderBy('id_evenement');

        return $this->paginer($requete, $lecteur, $filtres);
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    public function creer(User $auteur, array $donnees): Evenement
    {
        $timezone = $donnees['timezone_id'] ?? 'America/Toronto';
        $debut = Carbon::parse($donnees['date_debut'], $timezone)->utc();
        $fin = filled($donnees['date_fin'] ?? null)
            ? Carbon::parse($donnees['date_fin'], $timezone)->utc()
            : null;

        return Evenement::create([
            'id_utilisateur' => $auteur->id_utilisateur,
            ...$this->geo($auteur),
            'titre' => $donnees['titre'],
            'description' => $donnees['description'],
            'id_categorie' => $donnees['id_categorie'] ?? null,
            'portee' => $donnees['portee'] ?? $auteur->parametre?->portee_defaut ?? 'quartier',
            'date_debut' => $debut,
            'date_fin' => $fin,
            'timezone_id' => $timezone,
            'lieu' => $donnees['lieu'] ?? null,
            'capacite' => $donnees['capacite'] ?? null,
            'statut' => 'actif',
        ]);
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    public function mettreAJour(Evenement $evenement, array $donnees): Evenement
    {
        $timezone = $donnees['timezone_id'] ?? $evenement->timezone_id;
        $valeurs = Arr::only($donnees, [
            'titre', 'description', 'id_categorie', 'portee', 'lieu', 'capacite', 'statut',
        ]);

        if (array_key_exists('date_debut', $donnees)) {
            $valeurs['date_debut'] = Carbon::parse($donnees['date_debut'], $timezone)->utc();
        }

        if (array_key_exists('date_fin', $donnees)) {
            $valeurs['date_fin'] = filled($donnees['date_fin'])
                ? Carbon::parse($donnees['date_fin'], $timezone)->utc()
                : null;
        }

        $valeurs['timezone_id'] = $timezone;

        $nullable = ['id_categorie', 'date_fin', 'lieu', 'capacite'];

        $evenement->fill(array_filter(
            $valeurs,
            fn ($valeur, $cle) => $valeur !== null || in_array($cle, $nullable, true),
            ARRAY_FILTER_USE_BOTH,
        ));
        $evenement->save();

        return $evenement;
    }

    public function supprimer(Evenement $evenement): void
    {
        $evenement->delete();
    }

    public function inscrire(Evenement $evenement, User $utilisateur): InscriptionEvenement
    {
        $inscription = DB::transaction(function () use ($evenement, $utilisateur) {
            $existante = $evenement->inscriptions()
                ->where('id_utilisateur', $utilisateur->id_utilisateur)
                ->first();

            if ($existante !== null && $existante->statut === 'inscrit') {
                return $existante;
            }

            if ($evenement->capacite !== null) {
                $inscrits = $evenement->inscriptions()->where('statut', 'inscrit')->count();

                if ($inscrits >= $evenement->capacite) {
                    throw ValidationException::withMessages([
                        'capacite' => __('interface.event_full'),
                    ]);
                }
            }

            return $evenement->inscriptions()->updateOrCreate(
                ['id_utilisateur' => $utilisateur->id_utilisateur],
                ['statut' => 'inscrit'],
            );
        });

        if ($inscription->wasRecentlyCreated || $inscription->wasChanged('statut')) {
            $organisateur = $evenement->utilisateur;

            if ($organisateur !== null && (int) $organisateur->id_utilisateur !== (int) $utilisateur->id_utilisateur) {
                $this->notifications->envoyer($organisateur, 'evenement.inscription', [
                    'id_evenement' => $evenement->id_evenement,
                    'titre' => $evenement->titre,
                ]);
            }
        }

        return $inscription;
    }

    public function annulerInscription(Evenement $evenement, User $utilisateur): void
    {
        $evenement->inscriptions()
            ->where('id_utilisateur', $utilisateur->id_utilisateur)
            ->delete();
    }
}
