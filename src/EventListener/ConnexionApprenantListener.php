<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Apprenant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Mémorise la date de dernière connexion d'un apprenant (statistiques
 * d'usage du back-office).
 */
#[AsEventListener]
final class ConnexionApprenantListener
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        $apprenant = $event->getUser();
        if (!$apprenant instanceof Apprenant) {
            return;
        }

        $apprenant->setDerniereConnexion(new \DateTimeImmutable());
        $this->entityManager->flush();
    }
}
