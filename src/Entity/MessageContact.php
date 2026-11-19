<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MessageContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Message reçu via le formulaire de contact (§ 2.8).
 */
#[ORM\Entity(repositoryClass: MessageContactRepository::class)]
#[ORM\Table(name: 'message_contact')]
class MessageContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Merci d\'indiquer votre nom.')]
    #[Assert\Length(max: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Merci d\'indiquer votre prénom.')]
    #[Assert\Length(max: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Merci d\'indiquer votre adresse e-mail.')]
    #[Assert\Email(message: "L'adresse e-mail « {{ value }} » n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: 'Merci d\'indiquer un sujet.')]
    #[Assert\Length(max: 200)]
    private ?string $sujet = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le message ne peut pas être vide.')]
    #[Assert\Length(min: 10, minMessage: 'Le message doit contenir au moins {{ limit }} caractères.')]
    private ?string $message = null;

    #[ORM\Column]
    private bool $lu = false;

    #[ORM\Column]
    private bool $traite = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $noteInterne = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $adresseIp = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __toString(): string
    {
        return sprintf('%s : %s', $this->getNomComplet(), $this->sujet ?? '');
    }

    public function getNomComplet(): string
    {
        return trim(($this->prenom ?? '').' '.($this->nom ?? ''));
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getSujet(): ?string
    {
        return $this->sujet;
    }

    public function setSujet(string $sujet): static
    {
        $this->sujet = $sujet;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function isLu(): bool
    {
        return $this->lu;
    }

    public function setLu(bool $lu): static
    {
        $this->lu = $lu;

        return $this;
    }

    public function isTraite(): bool
    {
        return $this->traite;
    }

    public function setTraite(bool $traite): static
    {
        $this->traite = $traite;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
