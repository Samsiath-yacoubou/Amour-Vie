<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ParametreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

/**
 * Paramètres généraux du site (§ 3.10 et § 3.12).
 *
 * Enregistrement unique : identité, hero de l'accueil, coordonnées,
 * réseaux sociaux, footer, SEO et pages légales.
 */
#[ORM\Entity(repositoryClass: ParametreRepository::class)]
#[ORM\Table(name: 'parametre')]
#[Vich\Uploadable]
class Parametre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // ---------------------------------------------------------------- Identité

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le nom du site est obligatoire.')]
    private string $nomSite = 'Amour&Vie ABMS';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slogan = 'Programme des jeunes animé par les jeunes et pour les jeunes';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoName = null;

    #[Vich\UploadableField(mapping: 'parametre_fichier', fileNameProperty: 'logoName')]
    #[Assert\Image(maxSize: '4M', mimeTypesMessage: 'Le logo doit être une image (JPG, PNG, SVG, WebP).')]
    private ?File $logoFile = null;

    /** Variante claire du logo, utilisée sur les fonds sombres (footer, hero). */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoBlancName = null;

    #[Vich\UploadableField(mapping: 'parametre_fichier', fileNameProperty: 'logoBlancName')]
    #[Assert\Image(maxSize: '4M', mimeTypesMessage: 'Le logo doit être une image (JPG, PNG, SVG, WebP).')]
    private ?File $logoBlancFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $faviconName = null;

    #[Vich\UploadableField(mapping: 'parametre_fichier', fileNameProperty: 'faviconName')]
    #[Assert\Image(maxSize: '1M', mimeTypesMessage: 'Le favicon doit être une image (PNG, ICO, SVG).')]
    private ?File $faviconFile = null;

    // ------------------------------------------------------- Hero de l'accueil

    #[ORM\Column(length: 255)]
    private string $heroTitre = 'Les jeunes au coeur du changement';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $heroSousTitre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heroImageName = null;

    #[Vich\UploadableField(mapping: 'parametre_fichier', fileNameProperty: 'heroImageName')]
    #[Assert\Image(maxSize: '12M', mimeTypesMessage: "L'image doit être au format JPG, PNG ou WebP.")]
    private ?File $heroImageFile = null;

    /** URL d'une vidéo institutionnelle ; prioritaire sur l'image si renseignée. */
    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(message: "L'URL de la vidéo n'est pas valide.")]
    private ?string $heroVideoUrl = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $heroBouton1Libelle = 'Découvrir Amour&Vie';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heroBouton1Lien = '/qui-sommes-nous';

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $heroBouton2Libelle = 'Nos actions';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heroBouton2Lien = '/programmes';

    // ------------------------------------------------------------ Coordonnées

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $telephoneSecondaire = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email(message: "L'adresse e-mail « {{ value }} » n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $horaires = null;

    /** Code d'intégration Google Maps (iframe) affiché sur la page Contact. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $mapsEmbed = null;

    // -------------------------------------------------------- Réseaux sociaux

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL Facebook n'est pas valide.")]
    private ?string $facebook = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL LinkedIn n'est pas valide.")]
    private ?string $linkedin = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL X (Twitter) n'est pas valide.")]
    private ?string $twitter = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL YouTube n'est pas valide.")]
    private ?string $youtube = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL Instagram n'est pas valide.")]
    private ?string $instagram = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL TikTok n'est pas valide.")]
    private ?string $tiktok = null;

    /** Affiche sur l'accueil le fil des dernières publications du réseau Amour&Vie. */
    #[ORM\Column]
    private bool $afficherFilReseauxSociaux = false;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $filReseauxSociauxTitre = 'Sur les réseaux Amour&Vie';

    /**
     * Code d'intégration (iframe/script) du fil des réseaux sociaux, par
     * exemple un widget agrégeant les pages Facebook/Instagram de tout le
     * réseau du programme (Elfsight, Juicer, SociableKIT…).
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $filReseauxSociauxCode = null;

    // ------------------------------------------------------------------ Footer

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $footerTexte = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $copyright = '© Amour&Vie ABMS. Tous droits réservés.';

    // --------------------------------------------------------------------- SEO

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $seoTitre = null;

    #[ORM\Column(length: 320, nullable: true)]
    private ?string $seoDescription = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $seoMotsCles = null;

    /** Image utilisée par défaut lors des partages sur les réseaux sociaux. */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePartageName = null;

    #[Vich\UploadableField(mapping: 'parametre_fichier', fileNameProperty: 'imagePartageName')]
    #[Assert\Image(maxSize: '6M', mimeTypesMessage: "L'image doit être au format JPG, PNG ou WebP.")]
    private ?File $imagePartageFile = null;

    // ------------------------------------------------------------ Certificats e-learning

    /** Personne qui signe les certificats au nom de l'ABMS. */
    #[ORM\Column(length: 150, nullable: true)]
    private ?string $certificatSignataireNom = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $certificatSignataireFonction = null;

    /** Image de la signature (PNG transparent conseillé). */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $certificatSignatureName = null;

    #[Vich\UploadableField(mapping: 'parametre_fichier', fileNameProperty: 'certificatSignatureName')]
    #[Assert\Image(maxSize: '2M', mimeTypes: ['image/png', 'image/jpeg'], mimeTypesMessage: 'La signature doit être au format PNG ou JPG.')]
    private ?File $certificatSignatureFile = null;

    // ------------------------------------------------------------ Pages légales

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $mentionsLegales = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $politiqueConfidentialite = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $conditionsUtilisation = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __toString(): string
    {
        return $this->nomSite;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomSite(): string
    {
        return $this->nomSite;
    }

    public function setNomSite(string $nomSite): static
    {
        $this->nomSite = $nomSite;

        return $this;
    }

    public function getSlogan(): ?string
    {
        return $this->slogan;
    }

    public function setSlogan(?string $slogan): static
    {
        $this->slogan = $slogan;

        return $this;
    }

    public function getLogoName(): ?string
    {
        return $this->logoName;
    }

    public function setLogoName(?string $logoName): static
    {
        $this->logoName = $logoName;

        return $this;
    }

    public function getLogoFile(): ?File
    {
        return $this->logoFile;
    }

    public function setLogoFile(?File $logoFile): static
    {
        $this->logoFile = $logoFile;

        if (null !== $logoFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getLogoBlancName(): ?string
    {
        return $this->logoBlancName;
    }

    public function setLogoBlancName(?string $logoBlancName): static
    {
        $this->logoBlancName = $logoBlancName;

        return $this;
    }

    public function getLogoBlancFile(): ?File
    {
        return $this->logoBlancFile;
    }

    public function setLogoBlancFile(?File $logoBlancFile): static
    {
        $this->logoBlancFile = $logoBlancFile;

        if (null !== $logoBlancFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getFaviconName(): ?string
    {
        return $this->faviconName;
    }

    public function setFaviconName(?string $faviconName): static
    {
        $this->faviconName = $faviconName;

        return $this;
    }

    public function getFaviconFile(): ?File
    {
        return $this->faviconFile;
    }

    public function setFaviconFile(?File $faviconFile): static
    {
        $this->faviconFile = $faviconFile;

        if (null !== $faviconFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getHeroTitre(): string
    {
        return $this->heroTitre;
    }

    public function setHeroTitre(string $heroTitre): static
    {
        $this->heroTitre = $heroTitre;

        return $this;
    }

    public function getHeroSousTitre(): ?string
    {
        return $this->heroSousTitre;
    }

    public function setHeroSousTitre(?string $heroSousTitre): static
    {
        $this->heroSousTitre = $heroSousTitre;

        return $this;
    }

    public function getHeroImageName(): ?string
    {
        return $this->heroImageName;
    }

    public function setHeroImageName(?string $heroImageName): static
    {
        $this->heroImageName = $heroImageName;

        return $this;
    }

    public function getHeroImageFile(): ?File
    {
        return $this->heroImageFile;
    }

    public function setHeroImageFile(?File $heroImageFile): static
    {
        $this->heroImageFile = $heroImageFile;

        if (null !== $heroImageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getHeroVideoUrl(): ?string
    {
        return $this->heroVideoUrl;
    }

    public function setHeroVideoUrl(?string $heroVideoUrl): static
    {
        $this->heroVideoUrl = $heroVideoUrl;

        return $this;
    }

    public function getHeroBouton1Libelle(): ?string
    {
        return $this->heroBouton1Libelle;
    }

    public function setHeroBouton1Libelle(?string $heroBouton1Libelle): static
    {
        $this->heroBouton1Libelle = $heroBouton1Libelle;

        return $this;
    }

    public function getHeroBouton1Lien(): ?string
    {
        return $this->heroBouton1Lien;
    }

    public function setHeroBouton1Lien(?string $heroBouton1Lien): static
    {
        $this->heroBouton1Lien = $heroBouton1Lien;

        return $this;
    }

    public function getHeroBouton2Libelle(): ?string
    {
        return $this->heroBouton2Libelle;
    }

    public function setHeroBouton2Libelle(?string $heroBouton2Libelle): static
    {
        $this->heroBouton2Libelle = $heroBouton2Libelle;

        return $this;
    }

    public function getHeroBouton2Lien(): ?string
    {
        return $this->heroBouton2Lien;
    }

    public function setHeroBouton2Lien(?string $heroBouton2Lien): static
    {
        $this->heroBouton2Lien = $heroBouton2Lien;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getTelephoneSecondaire(): ?string
    {
        return $this->telephoneSecondaire;
    }

    public function setTelephoneSecondaire(?string $telephoneSecondaire): static
    {
        $this->telephoneSecondaire = $telephoneSecondaire;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getHoraires(): ?string
    {
        return $this->horaires;
    }

    public function setHoraires(?string $horaires): static
    {
        $this->horaires = $horaires;

        return $this;
    }

    public function getMapsEmbed(): ?string
    {
        return $this->mapsEmbed;
    }

    public function setMapsEmbed(?string $mapsEmbed): static
    {
        $this->mapsEmbed = $mapsEmbed;

        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): static
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getLinkedin(): ?string
    {
        return $this->linkedin;
    }

    public function setLinkedin(?string $linkedin): static
    {
        $this->linkedin = $linkedin;

        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): static
    {
        $this->twitter = $twitter;

        return $this;
    }

    public function getYoutube(): ?string
    {
        return $this->youtube;
    }

    public function setYoutube(?string $youtube): static
    {
        $this->youtube = $youtube;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): static
    {
        $this->instagram = $instagram;

        return $this;
    }

    public function getTiktok(): ?string
    {
        return $this->tiktok;
    }

    public function setTiktok(?string $tiktok): static
    {
        $this->tiktok = $tiktok;

        return $this;
    }

    public function isAfficherFilReseauxSociaux(): bool
    {
        return $this->afficherFilReseauxSociaux;
    }

    public function setAfficherFilReseauxSociaux(bool $afficherFilReseauxSociaux): static
    {
        $this->afficherFilReseauxSociaux = $afficherFilReseauxSociaux;

        return $this;
    }

    public function getFilReseauxSociauxTitre(): ?string
    {
        return $this->filReseauxSociauxTitre;
    }

    public function setFilReseauxSociauxTitre(?string $filReseauxSociauxTitre): static
    {
        $this->filReseauxSociauxTitre = $filReseauxSociauxTitre;

        return $this;
    }

    public function getFilReseauxSociauxCode(): ?string
    {
        return $this->filReseauxSociauxCode;
    }

    public function setFilReseauxSociauxCode(?string $filReseauxSociauxCode): static
    {
        $this->filReseauxSociauxCode = $filReseauxSociauxCode;

        return $this;
    }

    public function getFooterTexte(): ?string
    {
        return $this->footerTexte;
    }

    public function setFooterTexte(?string $footerTexte): static
    {
        $this->footerTexte = $footerTexte;

        return $this;
    }

    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    public function setCopyright(?string $copyright): static
    {
        $this->copyright = $copyright;

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

    public function getSeoMotsCles(): ?string
    {
        return $this->seoMotsCles;
    }

    public function setSeoMotsCles(?string $seoMotsCles): static
    {
        $this->seoMotsCles = $seoMotsCles;

        return $this;
    }

    public function getImagePartageName(): ?string
    {
        return $this->imagePartageName;
    }

    public function setImagePartageName(?string $imagePartageName): static
    {
        $this->imagePartageName = $imagePartageName;

        return $this;
    }

    public function getImagePartageFile(): ?File
    {
        return $this->imagePartageFile;
    }

    public function setImagePartageFile(?File $imagePartageFile): static
    {
        $this->imagePartageFile = $imagePartageFile;

        if (null !== $imagePartageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getCertificatSignataireNom(): ?string
    {
        return $this->certificatSignataireNom;
    }

    public function setCertificatSignataireNom(?string $certificatSignataireNom): static
    {
        $this->certificatSignataireNom = $certificatSignataireNom;

        return $this;
    }

    public function getCertificatSignataireFonction(): ?string
    {
        return $this->certificatSignataireFonction;
    }

    public function setCertificatSignataireFonction(?string $certificatSignataireFonction): static
    {
        $this->certificatSignataireFonction = $certificatSignataireFonction;

        return $this;
    }

    public function getCertificatSignatureName(): ?string
    {
        return $this->certificatSignatureName;
    }

    public function setCertificatSignatureName(?string $certificatSignatureName): static
    {
        $this->certificatSignatureName = $certificatSignatureName;

        return $this;
    }

    public function getCertificatSignatureFile(): ?File
    {
        return $this->certificatSignatureFile;
    }

    public function setCertificatSignatureFile(?File $certificatSignatureFile): static
    {
        $this->certificatSignatureFile = $certificatSignatureFile;

        if (null !== $certificatSignatureFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getMentionsLegales(): ?string
    {
        return $this->mentionsLegales;
    }

    public function setMentionsLegales(?string $mentionsLegales): static
    {
        $this->mentionsLegales = $mentionsLegales;

        return $this;
    }

    public function getPolitiqueConfidentialite(): ?string
    {
        return $this->politiqueConfidentialite;
    }

    public function setPolitiqueConfidentialite(?string $politiqueConfidentialite): static
    {
        $this->politiqueConfidentialite = $politiqueConfidentialite;

        return $this;
    }

    public function getConditionsUtilisation(): ?string
    {
        return $this->conditionsUtilisation;
    }

    public function setConditionsUtilisation(?string $conditionsUtilisation): static
    {
        $this->conditionsUtilisation = $conditionsUtilisation;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
