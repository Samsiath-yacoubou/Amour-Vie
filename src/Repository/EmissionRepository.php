<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Emission;
use App\Entity\Programme;
use App\Enum\TypeEmission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Emission>
 */
class EmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emission::class);
    }

    /**
     * @param array{type?: ?string, thematique?: ?string} $filtres
     *
     * @return Paginator<Emission>
     */
    public function rechercherPubliees(array $filtres = [], int $page = 1, int $parPage = 9): Paginator
    {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.publie = true')
            ->orderBy('e.dateDiffusion', 'DESC')
            ->addOrderBy('e.createdAt', 'DESC');

        if (!empty($filtres['type']) && null !== TypeEmission::tryFrom($filtres['type'])) {
            $qb->andWhere('e.type = :type')->setParameter('type', TypeEmission::from($filtres['type']));
        }

        if (!empty($filtres['thematique'])) {
            $qb->join('e.thematiques', 'th')
                ->andWhere('th.slug = :thematique')->setParameter('thematique', $filtres['thematique']);
        }

        $qb->setFirstResult(($page - 1) * $parPage)->setMaxResults($parPage);

        return new Paginator($qb->getQuery(), true);
    }

    /**
     * Dernière émission publiée d'un type donné (radio ou TV), pour mise en avant
     * sur la page d'accueil.
     */
    public function trouverDerniereParType(TypeEmission $type): ?Emission
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.publie = true')
            ->andWhere('e.type = :type')->setParameter('type', $type)
            ->orderBy('e.dateDiffusion', 'DESC')
            ->addOrderBy('e.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Emission[]
     */
    public function trouverPubliesParProgramme(Programme $programme, int $limite = 6): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.publie = true')
            ->andWhere('e.programme = :programme')->setParameter('programme', $programme)
            ->orderBy('e.dateDiffusion', 'DESC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }
}
