<?php

namespace App\Services;

use App\Enums\Langue;
use App\Enums\PorteeVisibilite;
use App\Models\Outil;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class OutilService
{
    public function __construct(
        private readonly FeedService $feed,
        private readonly PublicationService $medias,
    ) {}

    /**
     * @param  array{scope?: string|null, lang?: string|null, q?: string|null, par_page?: int}  $filtres
     */
    public function liste(?User $lecteur, array $filtres = []): LengthAwarePaginator
    {
        $scope = $this->feed->normaliserScope($filtres['scope'] ?? null);
        $parPage = max(1, min(50, (int) ($filtres['par_page'] ?? 12)));

        $requete = Outil::query()
            ->with([
                'proprietaire:id_utilisateur,nom,prenom,avatar_path',
                'categorie:id_categorie,nom,slug',
                'medias',
            ])
            ->where('statut', 'actif')
            ->orderByDesc('created_at')
            ->orderByDesc('id_outil');

        $this->feed->appliquerPortee($requete, $lecteur, $scope);

        if (! empty($filtres['lang']) && in_array($filtres['lang'], ['fr', 'en'], true)) {
            $requete->where('lang', $filtres['lang']);
        }

        if (! empty($filtres['q'])) {
            $recherche = '%'.$filtres['q'].'%';
            $requete->where(function ($q) use ($recherche) {
                $q->where('titre', 'like', $recherche)
                    ->orWhere('description', 'like', $recherche);
            });
        }

        return $requete->paginate($parPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $donnees
     * @param  array<int, UploadedFile>  $images
     */
    public function creer(User $proprietaire, array $donnees, array $images = []): Outil
    {
        return DB::transaction(function () use ($proprietaire, $donnees, $images) {
            $outil = Outil::create([
                'id_proprietaire' => $proprietaire->id_utilisateur,
                'id_categorie' => $donnees['id_categorie'] ?? null,
                'id_quartier' => $proprietaire->id_quartier,
                'id_ville' => $proprietaire->id_ville,
                'id_pays' => $proprietaire->ville?->region?->pays?->id_pays,
                'titre' => $donnees['titre'],
                'description' => $donnees['description'],
                'lang' => $donnees['lang'] ?? $proprietaire->locale?->value ?? Langue::FR->value,
                'portee' => $donnees['portee'] ?? $proprietaire->parametre?->portee_defaut ?? PorteeVisibilite::QUARTIER->value,
                'etat' => $donnees['etat'] ?? 'bon',
                'caution' => $donnees['caution'] ?? null,
                'disponible' => true,
                'statut' => 'actif',
                'version' => 1,
            ]);

            if ($images !== []) {
                $this->medias->ajouterMedias($outil, $images);
            }

            return $outil;
        });
    }

    /**
     * @param  array<string, mixed>  $donnees
     * @param  array<int, UploadedFile>  $images
     */
    public function mettreAJour(Outil $outil, array $donnees, array $images = []): Outil
    {
        return DB::transaction(function () use ($outil, $donnees, $images) {
            $autorisees = Arr::only($donnees, [
                'titre', 'description', 'id_categorie', 'lang', 'portee', 'etat', 'caution', 'disponible', 'statut',
            ]);

            $nullable = ['id_categorie', 'caution'];

            $outil->fill(array_filter(
                $autorisees,
                fn ($valeur, $cle) => $valeur !== null || in_array($cle, $nullable, true),
                ARRAY_FILTER_USE_BOTH,
            ));

            $outil->save();

            if ($images !== []) {
                $this->medias->ajouterMedias($outil, $images);
            }

            return $outil;
        });
    }

    public function supprimer(Outil $outil): void
    {
        foreach ($outil->medias as $media) {
            $this->medias->supprimerMedia($media);
        }

        $outil->delete();
    }
}
