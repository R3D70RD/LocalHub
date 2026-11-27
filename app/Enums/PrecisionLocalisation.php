<?php

namespace App\Enums;

/**
 * Niveau de précision d'une localisation exposée publiquement.
 *
 * `precise` n'est jamais exposé publiquement : une adresse exacte n'est
 * partagée qu'après acceptation d'un prêt ou d'une invitation.
 */
enum PrecisionLocalisation: string
{
    case QUARTIER = 'quartier';
    case VILLE = 'ville';
    case APPROX = 'approx';
    case PRECISE = 'precise';

    public function label(): string
    {
        return match ($this) {
            self::QUARTIER => 'Quartier seulement',
            self::VILLE => 'Ville seulement',
            self::APPROX => 'Position approximative',
            self::PRECISE => 'Position exacte (privé)',
        };
    }

    public function estPublique(): bool
    {
        return $this !== self::PRECISE;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->filter(fn (self $case) => $case->estPublique())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
