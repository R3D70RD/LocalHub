<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvisPretResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_avis_pret,
            'id_pret' => $this->id_pret,
            'note' => $this->note,
            'commentaire' => $this->commentaire,
            'auteur' => $this->auteur ? [
                'id' => $this->auteur->id_utilisateur,
                'nom' => $this->auteur->nom,
                'prenom' => $this->auteur->prenom,
            ] : null,
            'cible' => $this->cible ? [
                'id' => $this->cible->id_utilisateur,
                'nom' => $this->cible->nom,
                'prenom' => $this->cible->prenom,
                'fiabilite' => $this->cible->fiabilite,
            ] : null,
            'cree_le' => $this->created_at?->toIso8601String(),
        ];
    }
}
