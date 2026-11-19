<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Statuts du workflow éditorial (cahier des charges § 3.2).
 *
 * Contributeur -> BROUILLON -> EN_ATTENTE -> (Éditeur / Super admin) -> PUBLIE
 */
enum StatutArticle: string
{
    case BROUILLON = 'brouillon';
    case EN_ATTENTE = 'en_attente';
    case PLANIFIE = 'planifie';
    case PUBLIE = 'publie';
    case ARCHIVE = 'archive';

    public function libelle(): string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::EN_ATTENTE => 'En attente de validation',
            self::PLANIFIE => 'Programmé',
            self::PUBLIE => 'Publié',
            self::ARCHIVE => 'Archivé',
        };
    }

    public function couleur(): string
    {
        return match ($this) {
            self::BROUILLON => 'secondary',
            self::EN_ATTENTE => 'warning',
            self::PLANIFIE => 'info',
            self::PUBLIE => 'success',
            self::ARCHIVE => 'dark',
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
