<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TentativeQuizRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Tentative d'un apprenant au quiz d'un module. Seuls le score et le
 * résultat sont conservés, jamais le détail des réponses : un choix de
 * réponse peut en dire long sur la vie privée d'un jeune.
 */
#[ORM\Entity(repositoryClass: TentativeQuizRepository::class)]
#[ORM\Table(name: 'tentative_quiz')]
#[ORM\Index(name: 'idx_tentative_apprenant_module', columns: ['apprenant_id', 'module_id'])]
class TentativeQuiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Apprenant::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Apprenant $apprenant = null;

    #[ORM\ManyToOne(targetEntity: ModuleCours::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ModuleCours $module = null;

    #[ORM\Column]
    private int $score;

    #[ORM\Column]
    private int $total;

    #[ORM\Column]
    private bool $reussie;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct(Apprenant $apprenant, ModuleCours $module, int $score, int $total, bool $reussie)
    {
        $this->apprenant = $apprenant;
        $this->module = $module;
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

    public function getModule(): ?ModuleCours
    {
        return $this->module;
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
        return $this->total > 0 ? (int) round($this->score * 100 / $this->total) : 0;
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
