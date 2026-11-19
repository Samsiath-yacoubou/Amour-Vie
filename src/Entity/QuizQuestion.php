<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Question d'un quiz. N'a pas d'existence autonome hors de son Quiz.
 */
#[ORM\Entity]
#[ORM\Table(name: 'quiz_question')]
class QuizQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Quiz::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Quiz $quiz = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "L'énoncé de la question est obligatoire.")]
    private ?string $enonce = null;

    /** Affichée après réponse : le contenu pédagogique du quiz. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $explication = null;

    #[ORM\Column]
    private int $ordre = 0;

    /** @var Collection<int, QuizChoix> */
    #[ORM\OneToMany(mappedBy: 'question', targetEntity: QuizChoix::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['ordre' => 'ASC'])]
    private Collection $choix;

    public function __construct()
    {
        $this->choix = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->enonce ? mb_substr($this->enonce, 0, 60) : '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function getEnonce(): ?string
    {
        return $this->enonce;
    }

    public function setEnonce(string $enonce): static
    {
        $this->enonce = $enonce;

        return $this;
    }

    public function getExplication(): ?string
    {
        return $this->explication;
    }

    public function setExplication(?string $explication): static
    {
        $this->explication = $explication;

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

    /** @return Collection<int, QuizChoix> */
    public function getChoix(): Collection
    {
        return $this->choix;
    }

    public function addChoix(QuizChoix $choix): static
    {
        if (!$this->choix->contains($choix)) {
            $this->choix->add($choix);
            $choix->setQuestion($this);
        }

        return $this;
    }

    public function removeChoix(QuizChoix $choix): static
    {
        if ($this->choix->removeElement($choix) && $choix->getQuestion() === $this) {
            $choix->setQuestion(null);
        }

        return $this;
    }
}
