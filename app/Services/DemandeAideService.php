<?php

namespace App\Services;

use App\Enums\PorteeVisibilite;
use App\Models\DemandeAide;
use App\Models\OffreAide;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DemandeAideService extends ContenuService
{
    /**
     * @param  array{scope?: string|null, q?: string|null, par_page?: int}  $filtres
     */
    public function liste(?User $lecteur, array $filtres = []): LengthAwarePaginator
    {
        $requete = DemandeAide::query()
            ->with([
                'utilisateur:id_utilisateur,nom,prenom,avatar_path',
                'categorie:id_categorie,nom,slug',
            ])
            ->withCount('offres')
            ->whereIn('statut', ['ouverte', 'pourvue'])
            ->orderByDesc('urgent')
            ->orderByDesc('created_at')
            ->orderByDesc('id_demande_aide');

        return $this->paginer($requete, $lecteur, $filtres);
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    public function creer(User $auteur, array $donnees): DemandeAide
    {
        return DB::transaction(fn () => DemandeAide::create([
            'id_utilisateur' => $auteur->id_utilisateur,
            ...$this->geo($auteur),
            'titre' => $donnees['titre'],
            'description' => $donnees['description'],
            'id_categorie' => $donnees['id_categorie'] ?? null,
            'portee' => $donnees['portee'] ?? $auteur->parametre?->portee_defaut ?? PorteeVisibilite::QUARTIER->value,
            'urgent' => (bool) ($donnees['urgent'] ?? false),
            'date_limite' => $donnees['date_limite'] ?? null,
            'statut' => 'ouverte',
        ]));
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    public function mettreAJour(DemandeAide $demande, array $donnees): DemandeAide
    {
        $autorisees = Arr::only($donnees, [
            'titre', 'description', 'id_categorie', 'portee', 'urgent', 'date_limite', 'statut',
        ]);

        $nullable = ['id_categorie', 'date_limite'];

        $demande->fill(array_filter(
            $autorisees,
            fn ($valeur, $cle) => $valeur !== null || in_array($cle, $nullable, true),
            ARRAY_FILTER_USE_BOTH,
        ));
        $demande->save();

        return $demande;
    }

    public function supprimer(DemandeAide $demande): void
    {
        $demande->delete();
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    public function proposerOffre(DemandeAide $demande, User $auteur, array $donnees): OffreAide
    {
        $offre = OffreAide::create([
            'id_demande_aide' => $demande->id_demande_aide,
            'id_utilisateur' => $auteur->id_utilisateur,
            'id_quartier' => $demande->id_quartier,
            'id_ville' => $demande->id_ville,
            'id_pays' => $demande->id_pays,
            'titre' => $donnees['titre'] ?? $demande->titre,
            'description' => $donnees['description'],
            'portee' => $demande->portee,
            'statut' => 'proposee',
        ]);

        $destinataire = $demande->utilisateur;

        if ($destinataire !== null && (int) $destinataire->id_utilisateur !== (int) $auteur->id_utilisateur) {
            $this->notifications->envoyer($destinataire, 'aide.offre', [
                'id_demande_aide' => $demande->id_demande_aide,
                'titre' => $demande->titre,
                'apercu' => mb_substr($donnees['description'], 0, 120),
            ]);
        }

        return $offre;
    }
}
