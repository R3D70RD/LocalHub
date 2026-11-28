<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnonceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_annonce,
            'titre' => $this->titre,
            'description' => $this->description,
            'type_annonce' => $this->type_annonce,
            'prix' => $this->prix,
            'gratuite' => $this->estGratuite(),
            'statut' => $this->statut,
            'portee' => $this->portee?->value,
            'auteur' => [
                'id' => $this->utilisateur?->id_utilisateur,
                'nom' => $this->utilisateur?->nom,
                'prenom' => $this->utilisateur?->prenom,
                'avatar_path' => $this->utilisateur?->avatar_path,
            ],
            'categorie' => $this->categorie ? [
                'id' => $this->categorie->id_categorie,
                'nom' => $this->categorie->nom,
                'slug' => $this->categorie->slug,
            ] : null,
            'geo' => [
                'id_quartier' => $this->id_quartier,
                'id_ville' => $this->id_ville,
                'id_pays' => $this->id_pays,
            ],
            'offres_count' => $this->whenCounted('offres'),
            'offres' => OffreAnnonceResource::collection($this->whenLoaded('offres')),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'cree_le' => $this->created_at?->toIso8601String(),
        ];
    }
}
