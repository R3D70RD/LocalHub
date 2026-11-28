<?php

namespace App\Services;

use App\Models\Suivi;
use App\Models\User;

class SuiviService
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function suivre(User $utilisateur, string $type, int $idCible): Suivi
    {
        return Suivi::firstOrCreate([
            'id_utilisateur' => $utilisateur->id_utilisateur,
            'cible_type' => $type,
            'cible_id' => $idCible,
        ]);
    }

    public function nePlusSuivre(User $utilisateur, string $type, int $idCible): void
    {
        Suivi::query()
            ->where('id_utilisateur', $utilisateur->id_utilisateur)
            ->where('cible_type', $type)
            ->where('cible_id', $idCible)
            ->delete();
    }

    public function estSuivi(User $utilisateur, string $type, int $idCible): bool
    {
        return Suivi::query()
            ->where('id_utilisateur', $utilisateur->id_utilisateur)
            ->where('cible_type', $type)
            ->where('cible_id', $idCible)
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    public function notifierSuiveurs(string $type, int $idCible, ?User $auteur, string $notificationType, array $donnees): void
    {
        Suivi::query()
            ->where('cible_type', $type)
            ->where('cible_id', $idCible)
            ->when($auteur !== null, fn ($q) => $q->where('id_utilisateur', '!=', $auteur->id_utilisateur))
            ->with('utilisateur')
            ->get()
            ->each(function (Suivi $suivi) use ($notificationType, $donnees) {
                if ($suivi->utilisateur !== null) {
                    $this->notifications->envoyer($suivi->utilisateur, $notificationType, $donnees);
                }
            });
    }
}
