<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\StatutModeration;
use App\Repository\TemoignageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Témoignage soumis anonymement par un·e jeune, modéré avant publication.
 * Aucune photo côté formulaire public : une photo peut être ajoutée en
 * back-office après consentement explicite recueilli hors-ligne.
 */
#[ORM\Entity(repositoryClass: TemoignageRepository::class)]
#[ORM\Table(name: 'temoignage')]
class Temoignage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $pseudonyme = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 10, max: 35, notInRangeMessage: "L'âge doit être compris entre {{ min }} et {{ max }} ans.")]
    private ?int $age = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le témoignage ne peut pas être vide.')]
    #[Assert\Length(min: 20, minMessage: 'Le témoignage doit contenir au moins {{ limit }} caractères.')]
    private ?string $contenu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoName = null;

    #[ORM\Column(length: 20, enumType: StatutModeration::class)]
    private StatutModeration $statut = StatutModeration::EN_ATTENTE;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $noteInterne = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $adresseIp = null;

    #[ORM\ManyToOne(targetEntity: Programme::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Programme $programme = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __toString(): string
    {
        return $this->pseudonyme ?? 'Anonyme';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudonyme(): ?string
    {
        return $this->pseudonyme;
    }

    public function setPseudonyme(?string $pseudonyme): static
    {
        $this->pseudonyme = $pseudonyme;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;

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

    public function getPhotoName(): ?string
    {
        return $this->photoName;
    }

    public function setPhotoName(?string $photoName): static
    {
        $this->photoName = $photoName;

        return $this;
    }

    public function getStatut(): StatutModeration
    {
        return $this->statut;
    }

    public function setStatut(StatutModeration $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getNoteInterne(): ?string
    {
        return $this->noteInterne;
    }

    public function setNoteInterne(?string $noteInterne): static
    {
        $this->noteInterne = $noteInterne;

        return $this;
    }

    public function getAdresseIp(): ?string
    {
        return $this->adresseIp;
    }

    public function setAdresseIp(?string $adresseIp): static
    {
        $this->adresseIp = $adresseIp;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
