<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PublicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

/**
 * Document de la bibliothèque numérique (§ 2.6 et § 3.3).
 */
#[ORM\Entity(repositoryClass: PublicationRepository::class)]
#[ORM\Table(name: 'publication')]
#[ORM\Index(name: 'idx_publication_annee', columns: ['annee'])]
#[Vich\Uploadable]
class Publication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    private ?string $titre = null;

    #[Gedmo\Slug(fields: ['titre'])]
    #[ORM\Column(length: 280, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: TypePublication::class, inversedBy: 'publications')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Assert\NotNull(message: 'Le type de document est obligatoire.')]
    private ?TypePublication $type = null;

    #[ORM\ManyToOne(targetEntity: Programme::class, inversedBy: 'publications')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Programme $programme = null;

    /** @var Collection<int, Thematique> */
    #[ORM\ManyToMany(targetEntity: Thematique::class, inversedBy: 'publications')]
    #[ORM\JoinTable(name: 'publication_thematique')]
    private Collection $thematiques;

    #[ORM\Column]
    #[Assert\NotNull(message: "L'année est obligatoire.")]
    #[Assert\Range(min: 1990, max: 2100, notInRangeMessage: "L'année doit être comprise entre {{ min }} et {{ max }}.")]
    private ?int $annee = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichierName = null;

    #[Vich\UploadableField(
        mapping: 'publication_fichier',
        fileNameProperty: 'fichierName',
        size: 'fichierTaille',
        originalName: 'fichierOriginal',
    )]
    #[Assert\File(
        maxSize: '64M',
        mimeTypes: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        mimeTypesMessage: 'Le document doit être au format PDF ou Word.',
    )]
    private ?File $fichierFile = null;

    #[ORM\Column(nullable: true)]
    private ?int $fichierTaille = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichierOriginal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $couvertureName = null;

    #[Vich\UploadableField(mapping: 'publication_couverture', fileNameProperty: 'couvertureName')]
    #[Assert\Image(maxSize: '6M', mimeTypesMessage: 'La couverture doit être au format JPG, PNG ou WebP.')]
    private ?File $couvertureFile = null;

    #[ORM\Column]
    private int $nombreTelechargements = 0;

    #[ORM\Column]
    private bool $publie = true;

    #[ORM\Column]
    private bool $aLaUne = false;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->thematiques = new ArrayCollection();
        $this->annee = (int) date('Y');
    }

    public function __toString(): string
    {
        return $this->titre ?? '';
    }

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

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

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

    public function getType(): ?TypePublication
    {
        return $this->type;
    }

    public function setType(?TypePublication $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getProgramme(): ?Programme
    {
        return $this->programme;
    }

    public function setProgramme(?Programme $programme): static
    {
        $this->programme = $programme;

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

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): static
    {
        $this->annee = $annee;

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

    public function getFichierOriginal(): ?string
    {
        return $this->fichierOriginal;
    }

    public function setFichierOriginal(?string $fichierOriginal): static
    {
        $this->fichierOriginal = $fichierOriginal;

        return $this;
    }

    public function getCouvertureName(): ?string
    {
        return $this->couvertureName;
    }

    public function setCouvertureName(?string $couvertureName): static
    {
        $this->couvertureName = $couvertureName;

        return $this;
    }

    public function getCouvertureFile(): ?File
    {
        return $this->couvertureFile;
    }

    public function setCouvertureFile(?File $couvertureFile): static
    {
        $this->couvertureFile = $couvertureFile;

        if (null !== $couvertureFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getNombreTelechargements(): int
    {
        return $this->nombreTelechargements;
    }

    public function setNombreTelechargements(int $nombreTelechargements): static
    {
        $this->nombreTelechargements = $nombreTelechargements;

        return $this;
    }

    public function isPublie(): bool
    {
        return $this->publie;
    }

    public function setPublie(bool $publie): static
    {
        $this->publie = $publie;

        return $this;
    }

    public function isALaUne(): bool
    {
        return $this->aLaUne;
    }

    public function setALaUne(bool $aLaUne): static
    {
        $this->aLaUne = $aLaUne;

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
