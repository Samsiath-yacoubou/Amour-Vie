<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ModuleCoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Module d'un cours : quelques leçons, puis un quiz de validation facultatif.
 *
 * Le quiz réutilise les quiz pédagogiques existants (questions, choix,
 * explications saisis dans l'admin « Quiz »), mais ici la correction se fait
 * côté serveur et chaque tentative est enregistrée.
 */
#[ORM\Entity(repositoryClass: ModuleCoursRepository::class)]
#[ORM\Table(name: 'module_cours')]
class ModuleCours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cours::class, inversedBy: 'modules')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Choisis le cours auquel appartient ce module.')]
    private ?Cours $cours = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: 'Le titre du module est obligatoire.')]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /** @var Collection<int, Lecon> */
    #[ORM\OneToMany(mappedBy: 'module', targetEntity: Lecon::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['ordre' => 'ASC', 'id' => 'ASC'])]
    private Collection $lecons;

    #[ORM\ManyToOne(targetEntity: Quiz::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Quiz $quiz = null;

    #[ORM\Column]
    private int $ordre = 0;

    public function __construct()
    {
        $this->lecons = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s · %s', $this->cours?->getTitre() ?? '?', $this->titre ?? '');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCours(): ?Cours
    {
        return $this->cours;
    }

    public function setCours(?Cours $cours): static
    {
        $this->cours = $cours;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /** @return Collection<int, Lecon> */
    public function getLecons(): Collection
    {
        return $this->lecons;
    }

    public function addLecon(Lecon $lecon): static
    {
        if (!$this->lecons->contains($lecon)) {
            $this->lecons->add($lecon);
            $lecon->setModule($this);
        }

        return $this;
    }

    public function removeLecon(Lecon $lecon): static
    {
        $this->lecons->removeElement($lecon);

        return $this;
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

    /** Un quiz sans question ne peut pas servir de validation. */
    public function aUnQuiz(): bool
    {
        return null !== $this->quiz && $this->quiz->getQuestions()->count() > 0;
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
