<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_publication,
            'titre' => $this->titre,
            'description' => $this->description,
            'prix' => $this->prix,
            'portee' => $this->portee?->value,
            'statut' => $this->statut,
            'date_creation' => $this->date_creation?->toIso8601String(),
            'auteur' => [
                'id' => $this->utilisateur?->id_utilisateur,
                'nom' => $this->utilisateur?->nom,
                'prenom' => $this->utilisateur?->prenom,
                'avatar_path' => $this->utilisateur?->avatar_path,
            ],
            'categorie' => $this->category ? [
                'id' => $this->category->id_categorie,
                'nom' => $this->category->nom,
                'slug' => $this->category->slug,
            ] : null,
            'geo' => [
                'id_quartier' => $this->id_quartier,
                'id_ville' => $this->id_ville,
                'id_pays' => $this->id_pays,
            ],
            'medias' => $this->whenLoaded('medias', fn () => MediaResource::collection($this->medias)),
            'commentaires' => $this->whenLoaded('commentaires', fn () => CommentaireResource::collection($this->commentaires)),
            'commentaires_count' => $this->whenCounted('commentaires'),
            'reactions_count' => $this->whenCounted('reactions'),
        ];
    }
}
