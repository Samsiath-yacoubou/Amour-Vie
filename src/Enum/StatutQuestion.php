<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Statut d'une question anonyme soumise par un·e jeune à un·e pair-éducateur·rice.
 *
 * EN_ATTENTE (soumise) -> REPONDUE_PRIVEE ou PUBLIEE_FAQ (répondue, publiée ou non
 * comme FAQ publique) ou REJETEE (hors-sujet, spam, à réorienter).
 */
enum StatutQuestion: string
{
    case EN_ATTENTE = 'en_attente';
    case REPONDUE_PRIVEE = 'repondue_privee';
    case PUBLIEE_FAQ = 'publiee_faq';
    case REJETEE = 'rejetee';

    public function libelle(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::REPONDUE_PRIVEE => 'Répondue (privée)',
            self::PUBLIEE_FAQ => 'Publiée en FAQ',
            self::REJETEE => 'Rejetée',
        };
    }

    public function couleur(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'warning',
            self::REPONDUE_PRIVEE => 'info',
            self::PUBLIEE_FAQ => 'success',
            self::REJETEE => 'dark',
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
