<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InscriptionEvenementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_inscription_evenement,
            'id_evenement' => $this->id_evenement,
            'statut' => $this->statut,
            'utilisateur' => [
                'id' => $this->utilisateur?->id_utilisateur,
                'nom' => $this->utilisateur?->nom,
                'prenom' => $this->utilisateur?->prenom,
            ],
            'cree_le' => $this->created_at?->toIso8601String(),
        ];
    }
}
