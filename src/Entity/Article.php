<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\StatutArticle;
use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

/**
 * Contenu éditorial : actualité, événement, communiqué, témoignage,
 * analyse, story ou interview (§ 2.5 et § 3.2).
 */
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Table(name: 'article')]
#[ORM\Index(name: 'idx_article_statut_date', columns: ['statut', 'date_publication'])]
#[Vich\Uploadable]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'Le titre ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $titre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sousTitre = null;

    #[Gedmo\Slug(fields: ['titre'])]
    #[ORM\Column(length: 280, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le résumé est obligatoire : il apparaît dans les listes et les partages sociaux.')]
    #[Assert\Length(max: 500, maxMessage: 'Le résumé ne doit pas dépasser {{ limit }} caractères.')]
    private ?string $resume = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le contenu de l'article est obligatoire.")]
    private ?string $contenu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[Vich\UploadableField(mapping: 'article_image', fileNameProperty: 'imageName')]
    #[Assert\Image(maxSize: '8M', mimeTypesMessage: "L'image doit être au format JPG, PNG ou WebP.")]
    private ?File $imageFile = null;

    /** Texte alternatif de l'image principale — accessibilité (§ 4.4). */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageAlt = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Utilisateur $auteur = null;

    /** Signature libre, si l'auteur n'est pas un compte du back-office. */
    #[ORM\Column(length: 150, nullable: true)]
    private ?string $auteurLibre = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $lieu = null;

    #[ORM\ManyToOne(targetEntity: Programme::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Programme $programme = null;

    #[ORM\ManyToOne(targetEntity: TypeArticle::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?TypeArticle $type = null;

    /** @var Collection<int, Thematique> */
    #[ORM\ManyToMany(targetEntity: Thematique::class, inversedBy: 'articles')]
    #[ORM\JoinTable(name: 'article_thematique')]
    private Collection $thematiques;

    /** @var Collection<int, Media> */
    #[ORM\ManyToMany(targetEntity: Media::class, inversedBy: 'articles')]
    #[ORM\JoinTable(name: 'article_media')]
    private Collection $galerie;

    #[ORM\Column(length: 20, enumType: StatutArticle::class)]
    private StatutArticle $statut = StatutArticle::BROUILLON;

    /**
     * Date de mise en ligne. Pour un article programmé, c'est la date à partir
     * de laquelle il devient visible sur le site public.
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotNull(message: 'La date de publication est obligatoire.')]
    private ?\DateTimeImmutable $datePublication = null;

    #[ORM\Column]
    private bool $aLaUne = false;

    #[ORM\Column]
    private int $nombreVues = 0;

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

    public function __construct()
    {
        $this->thematiques = new ArrayCollection();
        $this->galerie = new ArrayCollection();
        $this->datePublication = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->titre ?? '';
    }

    /** Vrai si l'article doit apparaître sur le site public à cet instant. */
    public function estVisible(): bool
    {
        if (StatutArticle::PUBLIE === $this->statut) {
            return true;
        }

        return StatutArticle::PLANIFIE === $this->statut
            && null !== $this->datePublication
            && $this->datePublication <= new \DateTimeImmutable();
    }

    /** Temps de lecture estimé, en minutes (base 200 mots/minute). */
    public function getTempsLecture(): int
    {
        $mots = str_word_count(strip_tags((string) $this->contenu));

        return max(1, (int) ceil($mots / 200));
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

    public function getSousTitre(): ?string
    {
        return $this->sousTitre;
    }

    public function setSousTitre(?string $sousTitre): static
    {
        $this->sousTitre = $sousTitre;

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

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

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

    public function getImageAlt(): ?string
    {
        return $this->imageAlt;
    }

    public function setImageAlt(?string $imageAlt): static
    {
        $this->imageAlt = $imageAlt;

        return $this;
    }

    public function getAuteur(): ?Utilisateur
    {
        return $this->auteur;
    }

    public function setAuteur(?Utilisateur $auteur): static
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getAuteurLibre(): ?string
    {
        return $this->auteurLibre;
    }

    public function setAuteurLibre(?string $auteurLibre): static
    {
        $this->auteurLibre = $auteurLibre;

        return $this;
    }

    /** Signature affichée sur le site public. */
    public function getSignature(): string
    {
        return $this->auteurLibre ?: ($this->auteur?->getNomComplet() ?? 'Amour&Vie ABMS');
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): static
    {
        $this->lieu = $lieu;

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

    public function getType(): ?TypeArticle
    {
        return $this->type;
    }

    public function setType(?TypeArticle $type): static
    {
        $this->type = $type;

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

    public function getStatut(): StatutArticle
    {
        return $this->statut;
    }

    public function setStatut(StatutArticle $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDatePublication(): ?\DateTimeImmutable
    {
        return $this->datePublication;
    }

    public function setDatePublication(\DateTimeImmutable $datePublication): static
    {
        $this->datePublication = $datePublication;

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

    public function getNombreVues(): int
    {
        return $this->nombreVues;
    }

    public function setNombreVues(int $nombreVues): static
    {
        $this->nombreVues = $nombreVues;

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
}
