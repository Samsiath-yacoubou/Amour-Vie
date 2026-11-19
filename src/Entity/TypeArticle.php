<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TypeArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Type éditorial : Actualité, Événement, Communiqué, Témoignage, Analyse, Story, Interview (§ 2.5).
 */
#[ORM\Entity(repositoryClass: TypeArticleRepository::class)]
#[ORM\Table(name: 'type_article')]
class TypeArticle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank(message: 'Le libellé du type est obligatoire.')]
    private ?string $nom = null;

    #[Gedmo\Slug(fields: ['nom'])]
    #[ORM\Column(length: 140, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: '/^#[0-9A-Fa-f]{6}$/', message: 'La couleur doit être au format hexadécimal, ex. #E85D75.')]
    private ?string $couleur = '#8B5CF6';

    #[ORM\Column]
    private int $ordre = 0;

    /** @var Collection<int, Article> */
    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Article::class)]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
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
}
