<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\NewsletterAbonneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Abonné·e à la newsletter Amour&Vie. Collecte uniquement pour l'instant :
 * l'envoi de campagnes n'est pas encore branché (MAILER_DSN local = null://null).
 */
#[ORM\Entity(repositoryClass: NewsletterAbonneRepository::class)]
#[ORM\Table(name: 'newsletter_abonne')]
class NewsletterAbonne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Merci d\'indiquer votre adresse e-mail.')]
    #[Assert\Email(message: "L'adresse e-mail « {{ value }} » n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column]
    private bool $actif = true;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $adresseIp = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __toString(): string
    {
        return $this->email ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

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
