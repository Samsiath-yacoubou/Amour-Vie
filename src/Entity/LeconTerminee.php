<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LeconTermineeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Leçon marquée comme lue par un apprenant.
 */
#[ORM\Entity(repositoryClass: LeconTermineeRepository::class)]
#[ORM\Table(name: 'lecon_terminee')]
#[ORM\UniqueConstraint(name: 'uniq_lecon_terminee', columns: ['apprenant_id', 'lecon_id'])]
class LeconTerminee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Apprenant::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Apprenant $apprenant = null;

    #[ORM\ManyToOne(targetEntity: Lecon::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Lecon $lecon = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct(Apprenant $apprenant, Lecon $lecon)
    {
        $this->apprenant = $apprenant;
        $this->lecon = $lecon;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApprenant(): ?Apprenant
    {
        return $this->apprenant;
    }

    public function getLecon(): ?Lecon
    {
        return $this->lecon;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
