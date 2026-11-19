<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\CategorieMembre;
use App\Repository\MembreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

/**
 * Membre du Conseil d'administration, de la Coordination ou de l'Équipe technique (§ 3.5).
 */
#[ORM\Entity(repositoryClass: MembreRepository::class)]
#[ORM\Table(name: 'membre')]
#[Vich\Uploadable]
class Membre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private ?string $nom = null;

    #[Gedmo\Slug(fields: ['prenom', 'nom'])]
    #[ORM\Column(length: 220, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'La fonction est obligatoire.')]
    private ?string $fonction = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $departement = null;

    #[ORM\Column(length: 40, enumType: CategorieMembre::class)]
    private CategorieMembre $categorie = CategorieMembre::EQUIPE_TECHNIQUE;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $biographie = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email(message: "L'adresse e-mail « {{ value }} » n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL LinkedIn n'est pas valide.")]
    private ?string $linkedin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoName = null;

    #[Vich\UploadableField(mapping: 'membre_photo', fileNameProperty: 'photoName')]
    #[Assert\Image(maxSize: '6M', mimeTypesMessage: 'La photo doit être au format JPG, PNG ou WebP.')]
    private ?File $photoFile = null;

    #[ORM\Column]
    private int $ordre = 0;

    #[ORM\Column]
    private bool $actif = true;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __toString(): string
    {
        return $this->getNomComplet();
    }

    public function getNomComplet(): string
    {
        return trim(($this->prenom ?? '').' '.($this->nom ?? ''));
    }

    public function getInitiales(): string
    {
        return mb_strtoupper(mb_substr((string) $this->prenom, 0, 1).mb_substr((string) $this->nom, 0, 1));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
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

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(string $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getDepartement(): ?string
    {
        return $this->departement;
    }

    public function setDepartement(?string $departement): static
    {
        $this->departement = $departement;

        return $this;
    }

    public function getCategorie(): CategorieMembre
    {
        return $this->categorie;
    }

    public function setCategorie(CategorieMembre $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getBiographie(): ?string
    {
        return $this->biographie;
    }

    public function setBiographie(?string $biographie): static
    {
        $this->biographie = $biographie;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getLinkedin(): ?string
    {
        return $this->linkedin;
    }

    public function setLinkedin(?string $linkedin): static
    {
        $this->linkedin = $linkedin;

        return $this;
    }

    public function getPhotoName(): ?string
    {
        return $this->photoName;
    }

    public function setPhotoName(?string $photoName): static
    {
        $this->photoName = $photoName;

        return $this;
    }

    public function getPhotoFile(): ?File
    {
        return $this->photoFile;
    }

    public function setPhotoFile(?File $photoFile): static
    {
        $this->photoFile = $photoFile;

        if (null !== $photoFile) {
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
