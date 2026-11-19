<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CentreJeune;
use App\Entity\Programme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CentreJeune>
 */
class CentreJeuneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CentreJeune::class);
    }

    /**
     * @return CentreJeune[]
     */
    public function trouverActifs(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.actif = true')
            ->orderBy('c.ordre', 'ASC')
            ->addOrderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function trouverActifParSlug(string $slug): ?CentreJeune
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.actif = true')
            ->andWhere('c.slug = :slug')->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return CentreJeune[]
     */
    public function trouverActifsParProgramme(Programme $programme): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.actif = true')
            ->andWhere('c.programme = :programme')->setParameter('programme', $programme)
            ->orderBy('c.ordre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Centres géolocalisés, sérialisés pour la carte publique.
     *
     * @return array<int, array<string, mixed>>
     */
    public function trouverPourCarte(): array
    {
        $centres = $this->createQueryBuilder('c')
            ->andWhere('c.actif = true')
            ->andWhere('c.latitude IS NOT NULL AND c.longitude IS NOT NULL')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(static fn (CentreJeune $centre) => [
            'id' => $centre->getId(),
            'nom' => $centre->getNom(),
            'slug' => $centre->getSlug(),
            'ville' => $centre->getVille(),
            'adresse' => $centre->getAdresse(),
            'telephone' => $centre->getTelephone(),
            'horaires' => $centre->getHoraires(),
            'latitude' => (float) $centre->getLatitude(),
            'longitude' => (float) $centre->getLongitude(),
        ], $centres);
    }
}
