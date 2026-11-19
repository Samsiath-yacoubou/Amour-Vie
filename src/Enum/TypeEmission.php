<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Type de diffusion d'une émission Amour&Vie.
 */
enum TypeEmission: string
{
    case RADIO = 'radio';
    case TV = 'tv';

    public function libelle(): string
    {
        return match ($this) {
            self::RADIO => 'Radio',
            self::TV => 'Télévision',
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::RADIO => 'fa fa-radio',
            self::TV => 'fa fa-tv',
        };
    }

    /**
     * @return array<string, self>
     */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->libelle()] = $case;
        }

        return $choices;
    }
}
