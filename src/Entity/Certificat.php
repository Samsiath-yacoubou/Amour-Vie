<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CertificatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Certificat délivré automatiquement à la fin d'un cours réussi.
 *
 * Le nom du titulaire et le titre du cours sont figés au moment de
 * l'émission : le certificat reste vérifiable même si le compte ou le cours
 * sont supprimés ensuite. Le numéro est aléatoire (non devinable) et sert à
 * la vérification publique (/verifier/{numero}).
 */
#[ORM\Entity(repositoryClass: CertificatRepository::class)]
#[ORM\Table(name: 'certificat')]
#[ORM\UniqueConstraint(name: 'uniq_certificat_apprenant_cours', columns: ['apprenant_id', 'cours_id'])]
class Certificat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private string $numero;

    #[ORM\ManyToOne(targetEntity: Apprenant::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Apprenant $apprenant = null;

    #[ORM\ManyToOne(targetEntity: Cours::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Cours $cours = null;

    #[ORM\Column(length: 210)]
    private string $nomTitulaire;

    #[ORM\Column(length: 200)]
    private string $titreCours;

    /** Pourcentage obtenu à l'examen final, s'il y en a un. */
    #[ORM\Column(nullable: true)]
    private ?int $scoreExamen = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $delivreLe;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $reemisLe = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $revoqueLe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motifRevocation = null;

    public function __construct(string $numero, Apprenant $apprenant, Cours $cours, ?int $scoreExamen)
    {
        $this->numero = $numero;
        $this->apprenant = $apprenant;
        $this->cours = $cours;
        $this->nomTitulaire = $apprenant->getNomComplet();
        $this->titreCours = (string) $cours->getTitre();
        $this->scoreExamen = $scoreExamen;
        $this->delivreLe = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->numero;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function getApprenant(): ?Apprenant
    {
        return $this->apprenant;
    }

    public function getCours(): ?Cours
    {
        return $this->cours;
    }

    public function getNomTitulaire(): string
    {
        return $this->nomTitulaire;
    }

    public function getTitreCours(): string
    {
        return $this->titreCours;
    }

    public function getScoreExamen(): ?int
    {
        return $this->scoreExamen;
    }

    public function getDelivreLe(): \DateTimeImmutable
    {
        return $this->delivreLe;
    }

    public function getReemisLe(): ?\DateTimeImmutable
    {
        return $this->reemisLe;
    }

    public function getRevoqueLe(): ?\DateTimeImmutable
    {
        return $this->revoqueLe;
    }

    public function getMotifRevocation(): ?string
    {
        return $this->motifRevocation;
    }

    public function estValide(): bool
    {
        return null === $this->revoqueLe;
    }

    public function revoquer(?string $motif): void
    {
        $this->revoqueLe = new \DateTimeImmutable();
        $this->motifRevocation = $motif ?: null;
    }

    /**
     * Réémission : on rétablit le certificat et on reprend le nom actuel du
     * compte (ex. après correction d'une faute). Le numéro ne change pas.
     */
    public function reemettre(): void
    {
        if (null !== $this->apprenant) {
            $this->nomTitulaire = $this->apprenant->getNomComplet();
        }
        if (null !== $this->cours) {
            $this->titreCours = (string) $this->cours->getTitre();
        }
        $this->revoqueLe = null;
        $this->motifRevocation = null;
        $this->reemisLe = new \DateTimeImmutable();
    }
}
