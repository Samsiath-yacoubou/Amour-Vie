<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ChiffreCleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Indicateur affiché sur la page d'accueil : années d'engagement,
 * programmes, communautés touchées, partenaires… (§ 2.2.B).
 */
#[ORM\Entity(repositoryClass: ChiffreCleRepository::class)]
#[ORM\Table(name: 'chiffre_cle')]
class ChiffreCle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La valeur est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'La valeur doit être positive.')]
    private ?int $valeur = null;

    /** Suffixe optionnel affiché après le nombre, ex. « + » ou « % ». */
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $suffixe = '+';

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le libellé est obligatoire.')]
    private ?string $libelle = null;

    #[ORM\Column(length: 60)]
    private string $icone = 'fa-chart-simple';

    #[ORM\Column]
    private int $ordre = 0;

    #[ORM\Column]
    private bool $actif = true;

    public function __toString(): string
    {
        return sprintf('%d %s', $this->valeur ?? 0, $this->libelle ?? '');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValeur(): ?int
    {
        return $this->valeur;
    }

    public function setValeur(int $valeur): static
    {
        $this->valeur = $valeur;

        return $this;
    }

    public function getSuffixe(): ?string
    {
        return $this->suffixe;
    }

    public function setSuffixe(?string $suffixe): static
    {
        $this->suffixe = $suffixe;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getIcone(): string
    {
        return $this->icone;
    }

    public function setIcone(string $icone): static
    {
        $this->icone = $icone;

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
}
