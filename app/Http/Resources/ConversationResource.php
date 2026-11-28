<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_conversation,
            'titre' => $this->titre,
            'membres' => $this->whenLoaded('membres', fn () => $this->membres->map(fn ($membre) => [
                'id' => $membre->utilisateur?->id_utilisateur,
                'nom' => $membre->utilisateur?->nom,
                'prenom' => $membre->utilisateur?->prenom,
                'dernier_lu_at' => $membre->dernier_lu_at?->toIso8601String(),
            ])),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'cree_le' => $this->created_at?->toIso8601String(),
            'mis_a_jour_le' => $this->updated_at?->toIso8601String(),
        ];
    }
}
