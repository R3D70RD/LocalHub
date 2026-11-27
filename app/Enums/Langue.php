<?php

namespace App\Enums;

enum Langue: string
{
    case FR = 'fr';
    case EN = 'en';

    public function label(): string
    {
        return match ($this) {
            self::FR => 'Français',
            self::EN => 'English',
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
