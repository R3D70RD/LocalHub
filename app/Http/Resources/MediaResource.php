<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_media,
            'chemin' => $this->chemin,
            'url' => $this->url(),
            'type' => $this->type,
            'alt' => $this->alt,
            'ordre' => $this->ordre,
        ];
    }
}
