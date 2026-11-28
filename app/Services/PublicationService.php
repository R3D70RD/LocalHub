<?php

namespace App\Services;

use App\Enums\PorteeVisibilite;
use App\Enums\TypeReaction;
use App\Models\Commentaire;
use App\Models\Media;
use App\Models\Publication;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PublicationService
{
    /**
     * @param  array<string, mixed>  $donnees
     * @param  array<int, UploadedFile>  $images
     */
    public function creer(User $auteur, array $donnees, array $images = []): Publication
    {
        $chemins = $this->stockerFichiers($images);

        try {
            return DB::transaction(function () use ($auteur, $donnees, $chemins) {
                $publication = Publication::create([
                    'titre' => $donnees['titre'],
                    'description' => $donnees['description'],
                    'prix' => $donnees['prix'] ?? null,
                    'date_heure' => $donnees['date_heure'] ?? null,
                    'id_categorie' => $donnees['id_categorie'] ?? null,
                    'id_utilisateur' => $auteur->id_utilisateur,
                    'id_quartier' => $auteur->id_quartier,
                    'id_ville' => $auteur->id_ville,
                    'id_pays' => $auteur->ville?->region?->pays?->id_pays,
                    'portee' => $donnees['portee'] ?? $auteur->parametre?->portee_defaut ?? PorteeVisibilite::QUARTIER->value,
                    'statut' => $donnees['statut'] ?? 'active',
                    'version' => 1,
                    'date_creation' => now(),
                ]);

                $this->attacherMedias($publication, $chemins);

                return $publication;
            });
        } catch (Throwable $exception) {
            $this->supprimerFichiers($chemins);
            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $donnees
     * @param  array<int, UploadedFile>  $images
     */
    public function mettreAJour(Publication $publication, array $donnees, array $images = []): Publication
    {
        $chemins = $this->stockerFichiers($images);

        try {
            return DB::transaction(function () use ($publication, $donnees, $chemins) {
                $autorisees = Arr::only($donnees, [
                    'titre', 'description', 'prix', 'date_heure', 'id_categorie', 'portee', 'statut',
                ]);

                // Les champs nullables peuvent être vidés explicitement ; les
                // champs obligatoires ignorent une valeur null.
                $nullable = ['prix', 'date_heure', 'id_categorie'];

                $publication->fill(array_filter(
                    $autorisees,
                    fn ($valeur, $cle) => $valeur !== null || in_array($cle, $nullable, true),
                    ARRAY_FILTER_USE_BOTH,
                ));

                $publication->save();

                $this->attacherMedias($publication, $chemins);

                return $publication;
            });
        } catch (Throwable $exception) {
            $this->supprimerFichiers($chemins);
            throw $exception;
        }
    }

    public function supprimer(Publication $publication): void
    {
        foreach ($publication->medias as $media) {
            Storage::disk('public')->delete($media->chemin);
        }

        $publication->delete();
    }

    public function commenter(Publication $publication, User $auteur, string $contenu): Commentaire
    {
        return $publication->commentaires()->create([
            'id_utilisateur' => $auteur->id_utilisateur,
            'contenu' => $contenu,
        ]);
    }

    /**
     * Ajoute ou retire une réaction. Retourne true si elle a été ajoutée.
     */
    public function basculerReaction(Publication $publication, User $auteur, TypeReaction $type): bool
    {
        $existante = Reaction::query()
            ->where('id_publication', $publication->id_publication)
            ->where('id_utilisateur', $auteur->id_utilisateur)
            ->where('type', $type->value)
            ->first();

        if ($existante !== null) {
            $existante->delete();

            return false;
        }

        Reaction::create([
            'id_publication' => $publication->id_publication,
            'id_utilisateur' => $auteur->id_utilisateur,
            'type' => $type->value,
        ]);

        return true;
    }

    /**
     * @param  array<int, UploadedFile>  $images
     */
    public function ajouterMedias(Model $cible, array $images): void
    {
        $chemins = $this->stockerFichiers($images);

        try {
            $this->attacherMedias($cible, $chemins);
        } catch (Throwable $exception) {
            $this->supprimerFichiers($chemins);
            throw $exception;
        }
    }

    public function supprimerMedia(Media $media): void
    {
        Storage::disk('public')->delete($media->chemin);
        $media->delete();
    }

    /**
     * @param  array<int, UploadedFile>  $images
     * @return array<int, string>
     */
    private function stockerFichiers(array $images): array
    {
        return collect($images)
            ->filter(fn ($image) => $image instanceof UploadedFile)
            ->map(fn (UploadedFile $image) => $image->store('publications', 'public'))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $chemins
     */
    private function attacherMedias(Model $cible, array $chemins): void
    {
        $ordre = (int) $cible->medias()->max('ordre');

        foreach ($chemins as $chemin) {
            $cible->medias()->create([
                'chemin' => $chemin,
                'type' => 'image',
                'ordre' => ++$ordre,
            ]);
        }
    }

    /**
     * @param  array<int, string>  $chemins
     */
    private function supprimerFichiers(array $chemins): void
    {
        foreach ($chemins as $chemin) {
            Storage::disk('public')->delete($chemin);
        }
    }
}
