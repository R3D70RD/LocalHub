<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_message,
            'contenu' => $this->contenu,
            'lu_at' => $this->lu_at?->toIso8601String(),
            'expediteur' => [
                'id' => $this->expediteur?->id_utilisateur,
                'nom' => $this->expediteur?->nom,
                'prenom' => $this->expediteur?->prenom,
            ],
            'cree_le' => $this->created_at?->toIso8601String(),
        ];
    }
}
