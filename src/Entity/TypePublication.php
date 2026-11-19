<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TypePublicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Type de document de la bibliothèque numérique : Rapport, Étude, Guide,
 * Note de politique, Brochure, Newsletter, Communiqué, Document institutionnel (§ 2.6).
 */
#[ORM\Entity(repositoryClass: TypePublicationRepository::class)]
#[ORM\Table(name: 'type_publication')]
class TypePublication
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

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $icone = 'fa-file-lines';

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: '/^#[0-9A-Fa-f]{6}$/', message: 'La couleur doit être au format hexadécimal, ex. #E3001B.')]
    private ?string $couleur = '#E3001B';

    #[ORM\Column]
    private int $ordre = 0;

    /** @var Collection<int, Publication> */
    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Publication::class)]
    private Collection $publications;

    public function __construct()
    {
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

    public function getIcone(): ?string
    {
        return $this->icone;
    }

    public function setIcone(?string $icone): static
    {
        $this->icone = $icone;

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

    /** @return Collection<int, Publication> */
    public function getPublications(): Collection
    {
        return $this->publications;
    }
}
