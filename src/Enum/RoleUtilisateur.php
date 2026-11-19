<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Rôles et permissions du back-office My Amour&Vie.
 */
enum RoleUtilisateur: string
{
    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    case EDITEUR = 'ROLE_EDITEUR';
    case GESTIONNAIRE = 'ROLE_GESTIONNAIRE';
    case CONTRIBUTEUR = 'ROLE_CONTRIBUTEUR';

    public function libelle(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super administrateur',
            self::EDITEUR => 'Éditeur',
            self::GESTIONNAIRE => 'Gestionnaire',
            self::CONTRIBUTEUR => 'Contributeur',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Accès complet à toutes les fonctionnalités',
            self::EDITEUR => 'Créer, modifier et publier des articles',
            self::GESTIONNAIRE => 'Gérer les contenus (programmes, partenaires, équipe, médiathèque)',
            self::CONTRIBUTEUR => 'Rédiger des articles, sans pouvoir les publier',
        };
    }

    /**
     * Libellé => valeur scalaire : contrairement aux autres énumérations,
     * les rôles sont stockés dans un simple tableau de chaînes (contrat de
     * UserInterface::getRoles()), pas dans une colonne typée enum.
     *
     * @return array<string, string>
     */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->libelle()] = $case->value;
        }

        return $choices;
    }
}
