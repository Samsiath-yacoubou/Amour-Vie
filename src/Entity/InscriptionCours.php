<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InscriptionCoursRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Inscription d'un apprenant à un cours, avec le point de reprise.
 */
#[ORM\Entity(repositoryClass: InscriptionCoursRepository::class)]
#[ORM\Table(name: 'inscription_cours')]
#[ORM\UniqueConstraint(name: 'uniq_inscription_apprenant_cours', columns: ['apprenant_id', 'cours_id'])]
class InscriptionCours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Apprenant::class, inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Apprenant $apprenant = null;

    #[ORM\ManyToOne(targetEntity: Cours::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Cours $cours = null;

    /** Dernière leçon ouverte : c'est là que « Reprendre » ramène le jeune. */
    #[ORM\ManyToOne(targetEntity: Lecon::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Lecon $derniereLecon = null;

    /** Renseignée quand tous les modules sont validés. */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $termineeAt = null;

    /** Date du dernier rappel envoyé pour un cours resté inachevé (un seul rappel par période d'inactivité). */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $rappelEnvoyeAt = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(Apprenant $apprenant, Cours $cours)
    {
        $this->apprenant = $apprenant;
        $this->cours = $cours;
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

    public function getDerniereLecon(): ?Lecon
    {
        return $this->derniereLecon;
    }

    public function setDerniereLecon(?Lecon $derniereLecon): static
    {
        $this->derniereLecon = $derniereLecon;

        return $this;
    }

    public function getTermineeAt(): ?\DateTimeImmutable
    {
        return $this->termineeAt;
    }

    public function setTermineeAt(?\DateTimeImmutable $termineeAt): static
    {
        $this->termineeAt = $termineeAt;

        return $this;
    }

    public function isTerminee(): bool
    {
        return null !== $this->termineeAt;
    }

    public function getRappelEnvoyeAt(): ?\DateTimeImmutable
    {
        return $this->rappelEnvoyeAt;
    }

    public function setRappelEnvoyeAt(?\DateTimeImmutable $rappelEnvoyeAt): static
    {
        $this->rappelEnvoyeAt = $rappelEnvoyeAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
