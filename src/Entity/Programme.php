<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProgrammeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

/**
 * Pilier d'action d'Amour&Vie ABMS : Centres Jeunes, Magazine, Émissions
 * Radio & TV, Pair-éducation, Campagnes.
 * Les programmes sont administrables : l'équipe peut en ajouter sans intervention technique.
 */
#[ORM\Entity(repositoryClass: ProgrammeRepository::class)]
#[ORM\Table(name: 'programme')]
#[Vich\Uploadable]
class Programme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Le nom du programme est obligatoire.')]
    private ?string $nom = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Le sigle est obligatoire.')]
    private ?string $sigle = null;

    #[Gedmo\Slug(fields: ['sigle', 'nom'])]
    #[ORM\Column(length: 210, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description courte est obligatoire.')]
    #[Assert\Length(max: 400, maxMessage: 'La description courte ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $descriptionCourte = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objectifs = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $activites = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resultats = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: '/^#[0-9A-Fa-f]{6}$/', message: 'La couleur doit être au format hexadécimal, ex. #E3001B.')]
    private ?string $couleur = '#E3001B';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoName = null;

    #[Vich\UploadableField(mapping: 'programme_logo', fileNameProperty: 'logoName')]
    #[Assert\Image(maxSize: '4M', mimeTypesMessage: 'Le logo doit être une image (JPG, PNG, SVG, WebP).')]
    private ?File $logoFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[Vich\UploadableField(mapping: 'programme_image', fileNameProperty: 'imageName')]
    #[Assert\Image(maxSize: '8M', mimeTypesMessage: 'L\'image doit être au format JPG, PNG ou WebP.')]
    private ?File $imageFile = null;

    #[ORM\Column]
    private int $ordre = 0;

    #[ORM\Column]
    private bool $actif = true;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $seoTitre = null;

    #[ORM\Column(length: 320, nullable: true)]
    private ?string $seoDescription = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, Article> */
    #[ORM\OneToMany(mappedBy: 'programme', targetEntity: Article::class)]
    #[ORM\OrderBy(['datePublication' => 'DESC'])]
    private Collection $articles;

    /** @var Collection<int, Publication> */
    #[ORM\OneToMany(mappedBy: 'programme', targetEntity: Publication::class)]
    #[ORM\OrderBy(['annee' => 'DESC'])]
    private Collection $publications;

    /** @var Collection<int, Media> */
    #[ORM\ManyToMany(targetEntity: Media::class)]
    #[ORM\JoinTable(name: 'programme_media')]
    private Collection $galerie;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->publications = new ArrayCollection();
        $this->galerie = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->sigle ?? $this->nom ?? '';
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

    public function getSigle(): ?string
    {
        return $this->sigle;
    }

    public function setSigle(string $sigle): static
    {
        $this->sigle = $sigle;

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

    public function getDescriptionCourte(): ?string
    {
        return $this->descriptionCourte;
    }

    public function setDescriptionCourte(string $descriptionCourte): static
    {
        $this->descriptionCourte = $descriptionCourte;

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

    public function getObjectifs(): ?string
    {
        return $this->objectifs;
    }

    public function setObjectifs(?string $objectifs): static
    {
        $this->objectifs = $objectifs;

        return $this;
    }

    public function getActivites(): ?string
    {
        return $this->activites;
    }

    public function setActivites(?string $activites): static
    {
        $this->activites = $activites;

        return $this;
    }

    public function getResultats(): ?string
    {
        return $this->resultats;
    }

    public function setResultats(?string $resultats): static
    {
        $this->resultats = $resultats;

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

    public function getLogoName(): ?string
    {
        return $this->logoName;
    }

    public function setLogoName(?string $logoName): static
    {
        $this->logoName = $logoName;

        return $this;
    }

    public function getLogoFile(): ?File
    {
        return $this->logoFile;
    }

    public function setLogoFile(?File $logoFile): static
    {
        $this->logoFile = $logoFile;

        if (null !== $logoFile) {
            // Force Doctrine à considérer l'entité comme modifiée pour déclencher l'upload.
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): static
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

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

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getSeoTitre(): ?string
    {
        return $this->seoTitre;
    }

    public function setSeoTitre(?string $seoTitre): static
    {
        $this->seoTitre = $seoTitre;

        return $this;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seoDescription;
    }

    public function setSeoDescription(?string $seoDescription): static
    {
        $this->seoDescription = $seoDescription;

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

    /** @return Collection<int, Media> */
    public function getGalerie(): Collection
    {
        return $this->galerie;
    }

    public function addGalerie(Media $media): static
    {
        if (!$this->galerie->contains($media)) {
            $this->galerie->add($media);
        }

        return $this;
    }

    public function removeGalerie(Media $media): static
    {
        $this->galerie->removeElement($media);

        return $this;
    }
}
