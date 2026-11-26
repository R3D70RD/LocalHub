<?php

namespace App\Enums;

enum TypeReaction: string
{
    case JAIME = 'j_aime';
    case UTILE = 'utile';
    case MERCI = 'merci';

    public function label(): string
    {
        return match ($this) {
            self::JAIME => "J'aime",
            self::UTILE => 'Utile',
            self::MERCI => 'Merci',
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
