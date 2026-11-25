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
        // Sujet null : page « Ajouter » d'EasyAdmin, qui vérifie la permission
        // d'entité avant que l'article n'existe. Sans ce cas, le voter
        // s'abstenait et Symfony refusait l'accès à TOUT le monde (403).
        return self::MODIFIER === $attribute && (null === $subject || $subject instanceof Article);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $utilisateur = $token->getUser();

        if (!$utilisateur instanceof Utilisateur) {
            return false;
        }

        // Création : tout membre de l'équipe peut rédiger un article (le
        // contributeur ne pourra que l'enregistrer en brouillon ou le
        // soumettre, voir ArticleCrudController).
        if (null === $subject) {
            return true;
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
