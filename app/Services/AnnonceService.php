<?php

namespace App\Services;

use App\Models\Annonce;
use App\Models\Message;
use App\Models\OffreAnnonce;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class AnnonceService extends ContenuService
{
    /**
     * @param  array{scope?: string|null, q?: string|null, par_page?: int}  $filtres
     */
    public function liste(?User $lecteur, array $filtres = []): LengthAwarePaginator
    {
        $requete = Annonce::query()
            ->with([
                'utilisateur:id_utilisateur,nom,prenom,avatar_path',
                'categorie:id_categorie,nom,slug',
            ])
            ->withCount('offres')
            ->where('statut', 'active')
            ->orderByDesc('created_at')
            ->orderByDesc('id_annonce');

        return $this->paginer($requete, $lecteur, $filtres);
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    public function creer(User $auteur, array $donnees): Annonce
    {
        $prix = $donnees['prix'] ?? null;
        $gratuit = $prix !== null && (float) $prix === 0.0;
        $type = $donnees['type_annonce'] ?? 'vente';

        // Un prix nul transforme une vente en don (échange gratuit), sauf pour
        // une recherche d'objet.
        if ($gratuit && $type !== 'recherche') {
            $type = 'don';
        }

        return Annonce::create([
            'id_utilisateur' => $auteur->id_utilisateur,
            ...$this->geo($auteur),
            'titre' => $donnees['titre'],
            'description' => $donnees['description'],
            'id_categorie' => $donnees['id_categorie'] ?? null,
            'portee' => $donnees['portee'] ?? $auteur->parametre?->portee_defaut ?? 'quartier',
            'type_annonce' => $type,
            'prix' => $prix,
            'statut' => 'active',
        ]);
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    public function mettreAJour(Annonce $annonce, array $donnees): Annonce
    {
        $autorisees = Arr::only($donnees, [
            'titre', 'description', 'id_categorie', 'portee', 'type_annonce', 'prix', 'statut',
        ]);

        $nullable = ['id_categorie', 'prix'];

        $annonce->fill(array_filter(
            $autorisees,
            fn ($valeur, $cle) => $valeur !== null || in_array($cle, $nullable, true),
            ARRAY_FILTER_USE_BOTH,
        ));

        if ($annonce->prix !== null && (float) $annonce->prix === 0.0 && $annonce->type_annonce === 'vente') {
            $annonce->type_annonce = 'don';
        }

        $annonce->save();

        return $annonce;
    }

    public function supprimer(Annonce $annonce): void
    {
        $annonce->delete();
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    public function proposerOffre(Annonce $annonce, User $auteur, array $donnees): OffreAnnonce
    {
        $offre = OffreAnnonce::create([
            'id_annonce' => $annonce->id_annonce,
            'id_utilisateur' => $auteur->id_utilisateur,
            'montant' => $donnees['montant'] ?? null,
            'message' => $donnees['message'] ?? null,
            'statut' => 'proposee',
        ]);

        $destinataire = $annonce->utilisateur;

        if ($destinataire !== null && (int) $destinataire->id_utilisateur !== (int) $auteur->id_utilisateur) {
            $this->notifications->envoyer($destinataire, 'annonce.offre', [
                'id_annonce' => $annonce->id_annonce,
                'titre' => $annonce->titre,
                'apercu' => mb_substr((string) ($donnees['message'] ?? ''), 0, 120),
            ]);
        }

        return $offre;
    }

    public function envoyerMessage(Annonce $annonce, User $expediteur, string $contenu): Message
    {
        $message = $annonce->messages()->create([
            'id_expediteur' => $expediteur->id_utilisateur,
            'contenu' => $contenu,
        ]);

        $destinataire = $annonce->utilisateur;

        if ($destinataire !== null && (int) $destinataire->id_utilisateur !== (int) $expediteur->id_utilisateur) {
            $this->notifications->envoyer($destinataire, 'annonce.message', [
                'id_annonce' => $annonce->id_annonce,
                'titre' => $annonce->titre,
                'apercu' => mb_substr($contenu, 0, 120),
            ]);
        }

        return $message;
    }
}
