<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RencontreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_rencontre,
            'titre' => $this->titre,
            'description' => $this->description,
            'date_heure' => $this->date_heure?->toIso8601String(),
            'lieu' => $this->lieu,
            'capacite' => $this->capacite,
            'inscrits_count' => $this->inscrits_count ?? null,
            'interets' => $this->interets ?? [],
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
            'membres' => RencontreMembreResource::collection($this->whenLoaded('membres')),
            'cree_le' => $this->created_at?->toIso8601String(),
        ];
    }
}
