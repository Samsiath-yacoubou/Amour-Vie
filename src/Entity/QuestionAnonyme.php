<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\StatutQuestion;
use App\Repository\QuestionAnonymeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Question anonyme posée par un·e jeune à un·e pair-éducateur·rice.
 * Sert à la fois « Espace jeunes : pose ta question » (réponse privée) et
 * « Ressources : FAQ » (question + réponse publiées si statut PUBLIEE_FAQ).
 * Aucun champ e-mail : anonymat total, pas de suivi individuel possible.
 */
#[ORM\Entity(repositoryClass: QuestionAnonymeRepository::class)]
#[ORM\Table(name: 'question_anonyme')]
class QuestionAnonyme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $pseudonyme = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La question ne peut pas être vide.')]
    #[Assert\Length(min: 10, minMessage: 'La question doit contenir au moins {{ limit }} caractères.')]
    private ?string $question = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reponse = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $repondantLibre = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Utilisateur $repondant = null;

    #[ORM\Column(length: 20, enumType: StatutQuestion::class)]
    private StatutQuestion $statut = StatutQuestion::EN_ATTENTE;

    /** @var Collection<int, Thematique> */
    #[ORM\ManyToMany(targetEntity: Thematique::class)]
    #[ORM\JoinTable(name: 'question_anonyme_thematique')]
    private Collection $thematiques;

    #[ORM\Column]
    private int $ordre = 0;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $adresseIp = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $repondueAt = null;

    public function __construct()
    {
        $this->thematiques = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->question ? mb_substr($this->question, 0, 60) : '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudonyme(): ?string
    {
        return $this->pseudonyme;
    }

    public function setPseudonyme(?string $pseudonyme): static
    {
        $this->pseudonyme = $pseudonyme;

        return $this;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(?string $reponse): static
    {
        $this->reponse = $reponse;

        return $this;
    }

    public function getRepondantLibre(): ?string
    {
        return $this->repondantLibre;
    }

    public function setRepondantLibre(?string $repondantLibre): static
    {
        $this->repondantLibre = $repondantLibre;

        return $this;
    }

    public function getRepondant(): ?Utilisateur
    {
        return $this->repondant;
    }

    public function setRepondant(?Utilisateur $repondant): static
    {
        $this->repondant = $repondant;

        return $this;
    }

    public function getStatut(): StatutQuestion
    {
        return $this->statut;
    }

    public function setStatut(StatutQuestion $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    /** @return Collection<int, Thematique> */
    public function getThematiques(): Collection
    {
        return $this->thematiques;
    }

    public function addThematique(Thematique $thematique): static
    {
        if (!$this->thematiques->contains($thematique)) {
            $this->thematiques->add($thematique);
        }

        return $this;
    }

    public function removeThematique(Thematique $thematique): static
    {
        $this->thematiques->removeElement($thematique);

        return $this;
    }

    public function getOrdre(): int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getAdresseIp(): ?string
    {
        return $this->adresseIp;
    }

    public function setAdresseIp(?string $adresseIp): static
    {
        $this->adresseIp = $adresseIp;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRepondueAt(): ?\DateTimeImmutable
    {
        return $this->repondueAt;
    }

    public function setRepondueAt(?\DateTimeImmutable $repondueAt): static
    {
        $this->repondueAt = $repondueAt;

        return $this;
    }
}
