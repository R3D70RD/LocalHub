<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SignalementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_signalement,
            'cible_type' => $this->cible_type,
            'cible_id' => $this->cible_id,
            'motif' => $this->motif,
            'description' => $this->description,
            'statut' => $this->statut,
            'signaleur' => $this->utilisateur ? [
                'id' => $this->utilisateur->id_utilisateur,
                'nom' => $this->utilisateur->nom,
                'prenom' => $this->utilisateur->prenom,
            ] : null,
            'moderateur' => $this->moderateur ? [
                'id' => $this->moderateur->id_utilisateur,
                'nom' => $this->moderateur->nom,
                'prenom' => $this->moderateur->prenom,
            ] : null,
            'cree_le' => $this->created_at?->toIso8601String(),
        ];
    }
}
