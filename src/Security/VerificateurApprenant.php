<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Apprenant;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Refuse la connexion d'un compte apprenant désactivé depuis le back-office.
 */
class VerificateurApprenant implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof Apprenant && !$user->isActif()) {
            throw new CustomUserMessageAccountStatusException('Ce compte a été désactivé. Contacte l\'équipe Amour&Vie si tu penses qu\'il s\'agit d\'une erreur.');
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
    }
}
