<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Regroupement des membres sur la page « Qui sommes-nous ? » (§ 2.3).
 */
enum CategorieMembre: string
{
    case CONSEIL_ADMINISTRATION = 'conseil_administration';
    case COORDINATION = 'coordination';
    case EQUIPE_TECHNIQUE = 'equipe_technique';

    public function libelle(): string
    {
        return match ($this) {
            self::CONSEIL_ADMINISTRATION => "Conseil d'administration",
            self::COORDINATION => 'Coordination',
            self::EQUIPE_TECHNIQUE => 'Équipe technique',
        };
    }

    /**
     * Libellé => instance d'enum : EasyAdmin et les formulaires Symfony
     * attendent le cas lui-même, pas sa valeur scalaire.
     *
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
