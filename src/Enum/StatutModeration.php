<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Statut de modération d'un contenu soumis publiquement (témoignage).
 */
enum StatutModeration: string
{
    case EN_ATTENTE = 'en_attente';
    case PUBLIE = 'publie';
    case REJETE = 'rejete';

    public function libelle(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::PUBLIE => 'Publié',
            self::REJETE => 'Rejeté',
        };
    }

    public function couleur(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'warning',
            self::PUBLIE => 'success',
            self::REJETE => 'dark',
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
