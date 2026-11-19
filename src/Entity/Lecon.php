<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LeconRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Leçon d'un module : un texte (lisible seul, pour les petits débits),
 * éventuellement complété d'une courte vidéo chargée à la demande.
 */
#[ORM\Entity(repositoryClass: LeconRepository::class)]
#[ORM\Table(name: 'lecon')]
class Lecon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ModuleCours::class, inversedBy: 'lecons')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Choisis le module auquel appartient cette leçon.')]
    private ?ModuleCours $module = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: 'Le titre de la leçon est obligatoire.')]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le contenu de la leçon est obligatoire.')]
    private ?string $contenu = null;

    /** Lien YouTube (ou autre) d'une courte vidéo, jamais chargée sans l'accord du jeune. */
    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(message: 'Le lien de la vidéo n\'est pas valide.', requireTld: true)]
    private ?string $videoUrl = null;

    #[ORM\Column]
    #[Assert\Positive]
    private int $dureeMinutes = 5;

    #[ORM\Column]
    private int $ordre = 0;

    public function __toString(): string
    {
        return $this->titre ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModule(): ?ModuleCours
    {
        return $this->module;
    }

    public function setModule(?ModuleCours $module): static
    {
        $this->module = $module;

        return $this;
    }

    public function getCours(): ?Cours
    {
        return $this->module?->getCours();
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

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): static
    {
        $this->videoUrl = $videoUrl ?: null;

        return $this;
    }

    /**
     * Adresse intégrable pour une vidéo YouTube (domaine sans cookie), ou
     * null si le lien n'est pas reconnu : on affichera alors un simple lien.
     */
    public function getVideoIntegrable(): ?string
    {
        if (null === $this->videoUrl) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $this->videoUrl, $m)) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1];
        }

        return null;
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

    public function getOrdre(): int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }
}
