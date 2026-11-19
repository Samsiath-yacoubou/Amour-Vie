<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TentativeExamenRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Tentative d'un apprenant à l'examen final d'un cours. Comme pour les quiz
 * de module, seuls le score et le résultat sont conservés.
 */
#[ORM\Entity(repositoryClass: TentativeExamenRepository::class)]
#[ORM\Table(name: 'tentative_examen')]
#[ORM\Index(name: 'idx_tentative_examen_apprenant_cours', columns: ['apprenant_id', 'cours_id'])]
class TentativeExamen
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Apprenant::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Apprenant $apprenant = null;

    #[ORM\ManyToOne(targetEntity: Cours::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Cours $cours = null;

    #[ORM\Column]
    private int $score;

    #[ORM\Column]
    private int $total;

    #[ORM\Column]
    private bool $reussie;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct(Apprenant $apprenant, Cours $cours, int $score, int $total, bool $reussie)
    {
        $this->apprenant = $apprenant;
        $this->cours = $cours;
        $this->score = $score;
        $this->total = $total;
        $this->reussie = $reussie;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApprenant(): ?Apprenant
    {
        return $this->apprenant;
    }

    public function getCours(): ?Cours
    {
        return $this->cours;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getPourcentage(): int
    {
        return $this->total > 0 ? (int) floor($this->score * 100 / $this->total) : 0;
    }

    public function isReussie(): bool
    {
        return $this->reussie;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
