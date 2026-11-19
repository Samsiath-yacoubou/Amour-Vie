<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\TypeEmission;
use App\Repository\EmissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

/**
 * Émission radio ou TV Amour&Vie. Le lecteur public embarque un lien externe
 * (YouTube, SoundCloud…) plutôt que d'héberger des fichiers audio/vidéo.
 */
#[ORM\Entity(repositoryClass: EmissionRepository::class)]
#[ORM\Table(name: 'emission')]
#[Vich\Uploadable]
class Emission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre de l'émission est obligatoire.")]
    private ?string $titre = null;

    #[Gedmo\Slug(fields: ['titre'])]
    #[ORM\Column(length: 280, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 20, enumType: TypeEmission::class)]
    private TypeEmission $type = TypeEmission::RADIO;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(message: "L'URL « {{ value }} » n'est pas valide.")]
    private ?string $urlExterne = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[Vich\UploadableField(mapping: 'emission_image', fileNameProperty: 'imageName')]
    #[Assert\Image(maxSize: '8M', mimeTypesMessage: "L'image doit être au format JPG, PNG ou WebP.")]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichierAudioName = null;

    /**
     * Fichier audio hébergé pour les émissions radio : lecteur natif au clic
     * sur la couverture, sans dépendre d'un lien externe (SoundCloud…).
     */
    #[Vich\UploadableField(mapping: 'emission_audio', fileNameProperty: 'fichierAudioName')]
    #[Assert\File(
        maxSize: '40M',
        mimeTypes: ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg'],
        mimeTypesMessage: 'Le fichier audio doit être au format MP3, WAV ou OGG.',
    )]
    private ?File $fichierAudioFile = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateDiffusion = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $animateurs = null;

    #[ORM\ManyToOne(targetEntity: Programme::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Programme $programme = null;

    /** @var Collection<int, Thematique> */
    #[ORM\ManyToMany(targetEntity: Thematique::class)]
    #[ORM\JoinTable(name: 'emission_thematique')]
    private Collection $thematiques;

    #[ORM\Column]
    private bool $publie = true;

    #[ORM\Column]
    private int $ordre = 0;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->thematiques = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->titre ?? '';
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

    public function getType(): TypeEmission
    {
        return $this->type;
    }

    public function setType(TypeEmission $type): static
    {
        $this->type = $type;

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

    public function getUrlExterne(): ?string
    {
        return $this->urlExterne;
    }

    public function setUrlExterne(?string $urlExterne): static
    {
        $this->urlExterne = $urlExterne;

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

    public function getFichierAudioName(): ?string
    {
        return $this->fichierAudioName;
    }

    public function setFichierAudioName(?string $fichierAudioName): static
    {
        $this->fichierAudioName = $fichierAudioName;

        return $this;
    }

    public function getFichierAudioFile(): ?File
    {
        return $this->fichierAudioFile;
    }

    public function setFichierAudioFile(?File $fichierAudioFile): static
    {
        $this->fichierAudioFile = $fichierAudioFile;

        if (null !== $fichierAudioFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getDateDiffusion(): ?\DateTimeImmutable
    {
        return $this->dateDiffusion;
    }

    public function setDateDiffusion(?\DateTimeImmutable $dateDiffusion): static
    {
        $this->dateDiffusion = $dateDiffusion;

        return $this;
    }

    public function getAnimateurs(): ?string
    {
        return $this->animateurs;
    }

    public function setAnimateurs(?string $animateurs): static
    {
        $this->animateurs = $animateurs;

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

    public function isPublie(): bool
    {
        return $this->publie;
    }

    public function setPublie(bool $publie): static
    {
        $this->publie = $publie;

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
}
