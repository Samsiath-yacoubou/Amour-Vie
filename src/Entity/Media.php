<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\TypeMedia;
use App\Repository\MediaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

/**
 * Élément de la médiathèque : photo, vidéo, audio ou document (§ 3.11).
 */
#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\Table(name: 'media')]
#[Vich\Uploadable]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: 'Le nom du média est obligatoire.')]
    private ?string $nom = null;

    #[ORM\Column(length: 30, enumType: TypeMedia::class)]
    private TypeMedia $type = TypeMedia::IMAGE;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichierName = null;

    #[Vich\UploadableField(
        mapping: 'media_fichier',
        fileNameProperty: 'fichierName',
        size: 'fichierTaille',
        mimeType: 'fichierMime',
        originalName: 'fichierOriginal',
    )]
    #[Assert\File(maxSize: '64M', maxSizeMessage: 'Le fichier ne doit pas dépasser {{ limit }}.')]
    private ?File $fichierFile = null;

    #[ORM\Column(nullable: true)]
    private ?int $fichierTaille = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $fichierMime = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichierOriginal = null;

    /** Texte alternatif — exigence d'accessibilité RGAA / WCAG 2.1 (§ 4.4). */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $legende = null;

    /** URL externe (YouTube, Vimeo) pour les vidéos non hébergées. */
    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(message: "L'URL « {{ value }} » n'est pas valide.")]
    private ?string $urlExterne = null;

    #[ORM\ManyToOne(targetEntity: Galerie::class, inversedBy: 'medias')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Galerie $galerie = null;

    #[ORM\Column]
    private int $ordre = 0;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, Article> */
    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: 'galerie')]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }

    /** Taille lisible par un humain, affichée dans la médiathèque. */
    public function getTailleLisible(): string
    {
        if (null === $this->fichierTaille) {
            return 'inconnue';
        }

        $unites = ['o', 'Ko', 'Mo', 'Go'];
        $taille = (float) $this->fichierTaille;
        $index = 0;
        while ($taille >= 1024 && $index < \count($unites) - 1) {
            $taille /= 1024;
            ++$index;
        }

        return sprintf('%s %s', number_format($taille, $index > 1 ? 1 : 0, ',', ' '), $unites[$index]);
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

    public function getType(): TypeMedia
    {
        return $this->type;
    }

    public function setType(TypeMedia $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getFichierName(): ?string
    {
        return $this->fichierName;
    }

    public function setFichierName(?string $fichierName): static
    {
        $this->fichierName = $fichierName;

        return $this;
    }

    public function getFichierFile(): ?File
    {
        return $this->fichierFile;
    }

    public function setFichierFile(?File $fichierFile): static
    {
        $this->fichierFile = $fichierFile;

        if (null !== $fichierFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getFichierTaille(): ?int
    {
        return $this->fichierTaille;
    }

    public function setFichierTaille(?int $fichierTaille): static
    {
        $this->fichierTaille = $fichierTaille;

        return $this;
    }

    public function getFichierMime(): ?string
    {
        return $this->fichierMime;
    }

    public function setFichierMime(?string $fichierMime): static
    {
        $this->fichierMime = $fichierMime;

        return $this;
    }

    public function getFichierOriginal(): ?string
    {
        return $this->fichierOriginal;
    }

    public function setFichierOriginal(?string $fichierOriginal): static
    {
        $this->fichierOriginal = $fichierOriginal;

        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): static
    {
        $this->alt = $alt;

        return $this;
    }

    public function getLegende(): ?string
    {
        return $this->legende;
    }

    public function setLegende(?string $legende): static
    {
        $this->legende = $legende;

        return $this;
    }

    public function getUrlExterne(): ?string
    {
        return $this->urlExterne;
    }

    public function setUrlExterne(?string $urlExterne): static
    {
        $this->urlExterne = $urlExterne;

        return $this;
    }

    public function getGalerie(): ?Galerie
    {
        return $this->galerie;
    }

    public function setGalerie(?Galerie $galerie): static
    {
        $this->galerie = $galerie;

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
}
