<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PretResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_pret,
            'statut' => $this->statut?->value,
            'version' => $this->version,
            'date_debut' => $this->date_debut?->toDateString(),
            'date_fin' => $this->date_fin?->toDateString(),
            'date_demande' => $this->date_demande?->toIso8601String(),
            'date_retour_effectif' => $this->date_retour_effectif?->toIso8601String(),
            'message' => $this->message,
            'outil' => $this->whenLoaded('outil', fn () => [
                'id' => $this->outil->id_outil,
                'titre' => $this->outil->titre,
                'etat' => $this->outil->etat,
            ]),
            'emprunteur' => $this->whenLoaded('emprunteur', fn () => [
                'id' => $this->emprunteur->id_utilisateur,
                'nom' => $this->emprunteur->nom,
                'prenom' => $this->emprunteur->prenom,
            ]),
            'proprietaire' => $this->whenLoaded('proprietaire', fn () => [
                'id' => $this->proprietaire->id_utilisateur,
                'nom' => $this->proprietaire->nom,
                'prenom' => $this->proprietaire->prenom,
            ]),
            'messages' => $this->whenLoaded('messages', fn () => MessageResource::collection($this->messages)),
        ];
    }
}
