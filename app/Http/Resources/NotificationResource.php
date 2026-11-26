<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_notification,
            'type' => $this->type,
            'data' => $this->data,
            'lue' => $this->estLue(),
            'lu_at' => $this->lu_at?->toIso8601String(),
            'cree_le' => $this->created_at?->toIso8601String(),
        ];
    }
}
