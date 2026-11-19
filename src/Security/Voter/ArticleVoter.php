<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Article;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Contrôle d'accès article par article (§ 3.13).
 *
 * Un contributeur ne peut consulter et modifier que ses propres articles.
 * À partir du rôle éditeur, l'accès porte sur l'ensemble des contenus.
 *
 * Ce vote complète le filtrage de la liste : sans lui, un contributeur
 * pourrait atteindre l'article d'un collègue en saisissant son URL.
 */
class ArticleVoter extends Voter
{
    public const MODIFIER = 'ARTICLE_MODIFIER';

    public function __construct(private readonly AccessDecisionManagerInterface $decisionManager)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::MODIFIER === $attribute && $subject instanceof Article;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $utilisateur = $token->getUser();

        if (!$utilisateur instanceof Utilisateur) {
            return false;
        }

        // Éditeurs et super administrateurs accèdent à tous les articles.
        if ($this->decisionManager->decide($token, ['ROLE_EDITEUR'])) {
            return true;
        }

        \assert($subject instanceof Article);

        // Un article sans auteur (import, migration) reste réservé aux éditeurs.
        return null !== $subject->getAuteur() && $subject->getAuteur() === $utilisateur;
    }
}
