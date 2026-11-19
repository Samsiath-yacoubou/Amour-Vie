<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ThematiqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Thématique transversale : Jeunesse, Paix, Démocratie, Gouvernance… (§ 2.5).
 * Administrable pour permettre l'ajout de nouvelles thématiques (§ 5.1).
 */
#[ORM\Entity(repositoryClass: ThematiqueRepository::class)]
#[ORM\Table(name: 'thematique')]
class Thematique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank(message: 'Le nom de la thématique est obligatoire.')]
    private ?string $nom = null;

    #[Gedmo\Slug(fields: ['nom'])]
    #[ORM\Column(length: 140, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: '/^#[0-9A-Fa-f]{6}$/', message: 'La couleur doit être au format hexadécimal, ex. #E3001B.')]
    private ?string $couleur = '#E3001B';

    #[ORM\Column]
    private int $ordre = 0;

    /** @var Collection<int, Article> */
    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: 'thematiques')]
    private Collection $articles;

    /** @var Collection<int, Publication> */
    #[ORM\ManyToMany(targetEntity: Publication::class, mappedBy: 'thematiques')]
    private Collection $publications;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->publications = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

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

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(?string $couleur): static
    {
        $this->couleur = $couleur;

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

    /** @return Collection<int, Article> */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    /** @return Collection<int, Publication> */
    public function getPublications(): Collection
    {
        return $this->publications;
    }
}
