<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OffreAnnonceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_offre_annonce,
            'id_annonce' => $this->id_annonce,
            'montant' => $this->montant,
            'message' => $this->message,
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
