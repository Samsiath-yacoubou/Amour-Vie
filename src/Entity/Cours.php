<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

/**
 * Cours de l'e-learning : une suite ordonnée de modules, chacun composé de
 * leçons et, éventuellement, d'un quiz de validation.
 */
#[ORM\Entity(repositoryClass: CoursRepository::class)]
#[ORM\Table(name: 'cours')]
#[Vich\Uploadable]
class Cours
{
    public const NIVEAU_DEBUTANT = 'debutant';
    public const NIVEAU_INTERMEDIAIRE = 'intermediaire';
    public const NIVEAU_AVANCE = 'avance';

    public const NIVEAUX = [
        'Débutant' => self::NIVEAU_DEBUTANT,
        'Intermédiaire' => self::NIVEAU_INTERMEDIAIRE,
        'Avancé' => self::NIVEAU_AVANCE,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: 'Le titre du cours est obligatoire.')]
    private ?string $titre = null;

    #[Gedmo\Slug(fields: ['titre'])]
    #[ORM\Column(length: 220, unique: true)]
    private ?string $slug = null;

    /** Phrase d'accroche affichée dans le catalogue. */
    #[ORM\Column(length: 300)]
    #[Assert\NotBlank(message: 'Le résumé est obligatoire.')]
    #[Assert\Length(max: 300)]
    private ?string $resume = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[Vich\UploadableField(mapping: 'cours_image', fileNameProperty: 'imageName')]
    #[Assert\Image(maxSize: '4M', mimeTypesMessage: "L'image doit être au format JPG, PNG ou WebP.")]
    private ?File $imageFile = null;

    /** Durée indicative pour suivre tout le cours. */
    #[ORM\Column]
    #[Assert\Positive]
    private int $dureeMinutes = 30;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: self::NIVEAUX)]
    private string $niveau = self::NIVEAU_DEBUTANT;

    /** Pourcentage minimal de bonnes réponses pour valider un quiz du cours. */
    #[ORM\Column]
    #[Assert\Range(min: 1, max: 100)]
    private int $seuilReussite = 70;

    /**
     * Examen final facultatif, passé une fois tous les modules validés. Sa
     * réussite (score ≥ seuil) conditionne la délivrance du certificat.
     */
    #[ORM\ManyToOne(targetEntity: Quiz::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Quiz $examenFinal = null;

    /** Nombre maximal de tentatives à l'examen final (0 = illimité). */
    #[ORM\Column(options: ['default' => 3])]
    #[Assert\PositiveOrZero]
    private int $tentativesExamenMax = 3;

    #[ORM\ManyToOne(targetEntity: Programme::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Programme $programme = null;

    /** @var Collection<int, ModuleCours> */
    #[ORM\OneToMany(mappedBy: 'cours', targetEntity: ModuleCours::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['ordre' => 'ASC', 'id' => 'ASC'])]
    private Collection $modules;

    /** Un cours n'est visible des jeunes qu'une fois son contenu validé par l'ABMS. */
    #[ORM\Column]
    private bool $publie = false;

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
        $this->modules = new ArrayCollection();
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

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(string $resume): static
    {
        $this->resume = $resume;

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

    public function getDureeMinutes(): int
    {
        return $this->dureeMinutes;
    }

    public function setDureeMinutes(int $dureeMinutes): static
    {
        $this->dureeMinutes = $dureeMinutes;

        return $this;
    }

    public function getNiveau(): string
    {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getNiveauLibelle(): string
    {
        return array_search($this->niveau, self::NIVEAUX, true) ?: $this->niveau;
    }

    public function getSeuilReussite(): int
    {
        return $this->seuilReussite;
    }

    public function setSeuilReussite(int $seuilReussite): static
    {
        $this->seuilReussite = $seuilReussite;

        return $this;
    }

    public function getExamenFinal(): ?Quiz
    {
        return $this->examenFinal;
    }

    public function setExamenFinal(?Quiz $examenFinal): static
    {
        $this->examenFinal = $examenFinal;

        return $this;
    }

    /** Un examen sans question ne peut pas servir de validation. */
    public function aUnExamenFinal(): bool
    {
        return null !== $this->examenFinal && $this->examenFinal->getQuestions()->count() > 0;
    }

    public function getTentativesExamenMax(): int
    {
        return $this->tentativesExamenMax;
    }

    public function setTentativesExamenMax(int $tentativesExamenMax): static
    {
        $this->tentativesExamenMax = $tentativesExamenMax;

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

    /** @return Collection<int, ModuleCours> */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    public function addModule(ModuleCours $module): static
    {
        if (!$this->modules->contains($module)) {
            $this->modules->add($module);
            $module->setCours($this);
        }

        return $this;
    }

    public function removeModule(ModuleCours $module): static
    {
        $this->modules->removeElement($module);

        return $this;
    }

    /** @return list<Lecon> toutes les leçons du cours, dans l'ordre de lecture */
    public function getLecons(): array
    {
        $lecons = [];
        foreach ($this->modules as $module) {
            foreach ($module->getLecons() as $lecon) {
                $lecons[] = $lecon;
            }
        }

        return $lecons;
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
