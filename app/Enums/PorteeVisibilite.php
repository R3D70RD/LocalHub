<?php

namespace App\Enums;

/**
 * Portée de visibilité d'un contenu.
 *
 * Par défaut, tout contenu est visible dans le quartier de l'auteur.
 * L'auteur peut explicitement élargir à la ville ou au pays.
 */
enum PorteeVisibilite: string
{
    case QUARTIER = 'quartier';
    case VILLE = 'ville';
    case PAYS = 'pays';

    public function label(): string
    {
        return match ($this) {
            self::QUARTIER => 'Mon quartier',
            self::VILLE => 'Toute la ville',
            self::PAYS => 'Tout le pays',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
