<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Media;
use App\Enum\TypeMedia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    /**
     * @return Media[]
     */
    public function trouverParType(TypeMedia $type, int $limite = 24): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.type = :type')->setParameter('type', $type)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }

    public function compterTout(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
