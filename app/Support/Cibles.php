<?php

namespace App\Support;

use App\Models\Annonce;
use App\Models\DemandeAide;
use App\Models\Evenement;
use App\Models\Outil;
use App\Models\Publication;
use App\Models\Rencontre;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Table de correspondance entre les types courts stockés en base
 * (`signalement.cible_type`, `suivi.cible_type`) et les modèles Eloquent.
 */
class Cibles
{
    public const TYPES = [
        'publication' => Publication::class,
        'outil' => Outil::class,
        'demande_aide' => DemandeAide::class,
        'annonce' => Annonce::class,
        'evenement' => Evenement::class,
        'rencontre' => Rencontre::class,
        'utilisateur' => User::class,
    ];

    /**
     * @return array<int, string>
     */
    public static function cles(): array
    {
        return array_keys(self::TYPES);
    }

    public static function classe(string $type): ?string
    {
        return self::TYPES[$type] ?? null;
    }

    public static function resoudre(string $type, int $id): ?Model
    {
        $classe = self::classe($type);

        return $classe === null ? null : $classe::query()->find($id);
    }
}
