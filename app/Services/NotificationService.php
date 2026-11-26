<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * @param  array<string, mixed>  $donnees
     */
    public function envoyer(User $destinataire, string $type, array $donnees): Notification
    {
        return $destinataire->notifications()->create([
            'type' => $type,
            'data' => $donnees,
        ]);
    }
}
