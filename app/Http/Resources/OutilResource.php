<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutilResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_outil,
            'titre' => $this->titre,
            'description' => $this->description,
            'etat' => $this->etat,
            'caution' => $this->caution,
            'disponible' => $this->disponible,
            'statut' => $this->statut,
            'lang' => $this->lang?->value,
            'portee' => $this->portee?->value,
            'version' => $this->version,
            'proprietaire' => [
                'id' => $this->proprietaire?->id_utilisateur,
                'nom' => $this->proprietaire?->nom,
                'prenom' => $this->proprietaire?->prenom,
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
            'medias' => $this->whenLoaded('medias', fn () => MediaResource::collection($this->medias)),
        ];
    }
}
