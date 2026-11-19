<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ApprenantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Compte d'un jeune inscrit à l'e-learning (/apprendre).
 *
 * Volontairement distinct d'Utilisateur (équipe ABMS) : un apprenant n'a
 * jamais accès au back-office, et ses comptes vivent derrière leur propre
 * pare-feu. On ne collecte que le nécessaire : nom pour le certificat,
 * e-mail pour la connexion, téléphone facultatif.
 */
#[ORM\Entity(repositoryClass: ApprenantRepository::class)]
#[ORM\Table(name: 'apprenant')]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec cette adresse e-mail.')]
class Apprenant implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Indique ton adresse e-mail.')]
    #[Assert\Email(message: 'Cette adresse e-mail n\'est pas valide.')]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Indique ton prénom.')]
    #[Assert\Length(max: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Indique ton nom.')]
    #[Assert\Length(max: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    private ?string $telephone = null;

    /** Date à laquelle le jeune a accepté l'usage de ses données. */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $consentementAt = null;

    #[ORM\Column]
    private bool $actif = true;

    /** Empreinte SHA-256 du jeton de réinitialisation (le jeton en clair n'est jamais stocké). */
    #[ORM\Column(length: 64, nullable: true)]
    private ?string $jetonReinitialisation = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $jetonExpireAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $derniereConnexion = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    /** @var Collection<int, InscriptionCours> */
    #[ORM\OneToMany(mappedBy: 'apprenant', targetEntity: InscriptionCours::class, cascade: ['remove'])]
    private Collection $inscriptions;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getNomComplet();
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
        $this->email = mb_strtolower(trim($email));

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        return ['ROLE_APPRENANT'];
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = trim($prenom);

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = trim($nom);

        return $this;
    }

    public function getNomComplet(): string
    {
        return trim(($this->prenom ?? '').' '.($this->nom ?? ''));
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = null !== $telephone && '' !== trim($telephone) ? trim($telephone) : null;

        return $this;
    }

    public function getConsentementAt(): ?\DateTimeImmutable
    {
        return $this->consentementAt;
    }

    public function setConsentementAt(\DateTimeImmutable $consentementAt): static
    {
        $this->consentementAt = $consentementAt;

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

    public function getJetonReinitialisation(): ?string
    {
        return $this->jetonReinitialisation;
    }

    public function getJetonExpireAt(): ?\DateTimeImmutable
    {
        return $this->jetonExpireAt;
    }

    public function definirJetonReinitialisation(?string $empreinte, ?\DateTimeImmutable $expireAt): static
    {
        $this->jetonReinitialisation = $empreinte;
        $this->jetonExpireAt = $expireAt;

        return $this;
    }

    public function getDerniereConnexion(): ?\DateTimeImmutable
    {
        return $this->derniereConnexion;
    }

    public function setDerniereConnexion(?\DateTimeImmutable $derniereConnexion): static
    {
        $this->derniereConnexion = $derniereConnexion;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, InscriptionCours> */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }
}
