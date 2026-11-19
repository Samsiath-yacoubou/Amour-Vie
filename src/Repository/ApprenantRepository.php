<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Apprenant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Apprenant>
 */
class ApprenantRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Apprenant::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Apprenant) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->flush();
    }

    public function trouverParEmail(string $email): ?Apprenant
    {
        return $this->findOneBy(['email' => mb_strtolower(trim($email))]);
    }

    /** Jeton en clair reçu par e-mail → compte, si le jeton est valide et non expiré. */
    public function trouverParJetonValide(string $jeton): ?Apprenant
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.jetonReinitialisation = :empreinte')->setParameter('empreinte', hash('sha256', $jeton))
            ->andWhere('a.jetonExpireAt > :maintenant')->setParameter('maintenant', new \DateTimeImmutable())
            ->andWhere('a.actif = true')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
