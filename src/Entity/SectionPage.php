<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SectionPageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

/**
 * Bloc de contenu éditorial rattaché à une page institutionnelle (§ 3.9).
 *
 * Permet à l'équipe d'ajouter de nouvelles sections — et même de nouvelles
 * pages — sans intervention technique, comme exigé au § 5.1.
 */
#[ORM\Entity(repositoryClass: SectionPageRepository::class)]
#[ORM\Table(name: 'section_page')]
#[ORM\UniqueConstraint(name: 'uniq_section_page_cle', columns: ['page', 'cle'])]
#[Vich\Uploadable]
class SectionPage
{
    public const PAGE_QUI_SOMMES_NOUS = 'qui-sommes-nous';
    public const PAGE_ACCUEIL = 'accueil';
    public const PAGE_CONTACT = 'contact';
    public const PAGE_ESPACE_JEUNES = 'espace-jeunes';
    public const PAGE_RESSOURCES = 'ressources';
    public const PAGE_TEMOIGNAGES = 'temoignages';

    /** @var array<string, string> Pages proposées par défaut dans le back-office. */
    public const PAGES = [
        'Accueil' => self::PAGE_ACCUEIL,
        'Qui sommes-nous ?' => self::PAGE_QUI_SOMMES_NOUS,
        'Contact' => self::PAGE_CONTACT,
        'Espace jeunes' => self::PAGE_ESPACE_JEUNES,
        'Ressources' => self::PAGE_RESSOURCES,
        'Témoignages' => self::PAGE_TEMOIGNAGES,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Page d'accueil du bloc, ex. « qui-sommes-nous ». */
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La page de rattachement est obligatoire.')]
    private ?string $page = self::PAGE_QUI_SOMMES_NOUS;

    /** Identifiant technique du bloc dans la page, ex. « histoire ». */
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La clé de la section est obligatoire.')]
    #[Assert\Regex(pattern: '/^[a-z0-9\-]+$/', message: 'La clé ne peut contenir que des minuscules, chiffres et tirets.')]
    private ?string $cle = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre de la section est obligatoire.')]
    private ?string $titre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sousTitre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[Vich\UploadableField(mapping: 'page_section_image', fileNameProperty: 'imageName')]
    #[Assert\Image(maxSize: '8M', mimeTypesMessage: "L'image doit être au format JPG, PNG ou WebP.")]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageAlt = null;

    #[ORM\ManyToOne(targetEntity: Galerie::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Galerie $galerie = null;

    #[ORM\Column]
    private int $ordre = 0;

    #[ORM\Column]
    private bool $actif = true;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __toString(): string
    {
        return $this->titre ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPage(): ?string
    {
        return $this->page;
    }

    public function setPage(string $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getCle(): ?string
    {
        return $this->cle;
    }

    public function setCle(string $cle): static
    {
        $this->cle = $cle;

        return $this;
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

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): static
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

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
