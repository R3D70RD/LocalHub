<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FilResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'titre' => $this->titre,
            'description' => $this->description,
            'date' => $this->date_publication,
            'portee' => $this->portee,
            'auteur' => $this->auteur ? [
                'id' => $this->auteur->id_utilisateur,
                'nom' => $this->auteur->nom,
                'prenom' => $this->auteur->prenom,
                'avatar_path' => $this->auteur->avatar_path,
            ] : null,
            'categorie' => $this->categorie ? [
                'id' => $this->categorie->id_categorie,
                'nom' => $this->categorie->nom,
                'slug' => $this->categorie->slug,
            ] : null,
        ];
    }
}
