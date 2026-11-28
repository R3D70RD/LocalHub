<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DemandeAideResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_demande_aide,
            'titre' => $this->titre,
            'description' => $this->description,
            'urgent' => (bool) $this->urgent,
            'date_limite' => $this->date_limite?->toDateString(),
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
            'offres' => OffreAideResource::collection($this->whenLoaded('offres')),
            'cree_le' => $this->created_at?->toIso8601String(),
        ];
    }
}
