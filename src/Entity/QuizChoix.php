<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Choix de réponse d'une question de quiz. N'a pas d'existence autonome
 * hors de sa QuizQuestion.
 */
#[ORM\Entity]
#[ORM\Table(name: 'quiz_choix')]
class QuizChoix
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: QuizQuestion::class, inversedBy: 'choix')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?QuizQuestion $question = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le libellé du choix est obligatoire.')]
    private ?string $libelle = null;

    #[ORM\Column]
    private bool $estCorrect = false;

    #[ORM\Column]
    private int $ordre = 0;

    public function __toString(): string
    {
        return $this->libelle ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?QuizQuestion
    {
        return $this->question;
    }

    public function setQuestion(?QuizQuestion $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function isEstCorrect(): bool
    {
        return $this->estCorrect;
    }

    public function setEstCorrect(bool $estCorrect): static
    {
        $this->estCorrect = $estCorrect;

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
}
