<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SectionPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SectionPage>
 */
class SectionPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SectionPage::class);
    }

    /**
     * Sections actives d'une page, indexées par clé pour un accès direct
     * depuis Twig : {{ sections.histoire.contenu }}.
     *
     * @return array<string, SectionPage>
     */
    public function trouverParPage(string $page): array
    {
        $sections = $this->createQueryBuilder('s')
            ->andWhere('s.page = :page')->setParameter('page', $page)
            ->andWhere('s.actif = true')
            ->orderBy('s.ordre', 'ASC')
            ->getQuery()
            ->getResult();

        $indexees = [];
        foreach ($sections as $section) {
            $indexees[$section->getCle()] = $section;
        }

        return $indexees;
    }
}
