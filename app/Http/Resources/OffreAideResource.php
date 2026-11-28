<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OffreAideResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_offre_aide,
            'id_demande_aide' => $this->id_demande_aide,
            'titre' => $this->titre,
            'description' => $this->description,
            'statut' => $this->statut,
            'auteur' => [
                'id' => $this->utilisateur?->id_utilisateur,
                'nom' => $this->utilisateur?->nom,
                'prenom' => $this->utilisateur?->prenom,
            ],
            'cree_le' => $this->created_at?->toIso8601String(),
        ];
    }
}
