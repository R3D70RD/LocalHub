<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvenementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_evenement,
            'titre' => $this->titre,
            'description' => $this->description,
            'date_debut' => $this->date_debut?->toIso8601String(),
            'date_fin' => $this->date_fin?->toIso8601String(),
            'timezone_id' => $this->timezone_id,
            'lieu' => $this->lieu,
            'capacite' => $this->capacite,
            'inscrits_count' => $this->inscrits_count ?? null,
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
            'inscriptions' => InscriptionEvenementResource::collection($this->whenLoaded('inscriptions')),
            'cree_le' => $this->created_at?->toIso8601String(),
        ];
    }
}
