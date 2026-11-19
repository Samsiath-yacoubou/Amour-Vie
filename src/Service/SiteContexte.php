<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Parametre;
use App\Repository\ParametreRepository;
use App\Repository\ProgrammeRepository;
use App\Repository\ThematiqueRepository;
use App\Repository\TypeArticleRepository;
use App\Repository\TypePublicationRepository;

/**
 * Données présentes sur toutes les pages : paramètres généraux, programmes
 * du menu, taxonomies des filtres.
 *
 * Exposé à Twig sous la variable globale « site ». Le chargement est
 * paresseux : une page qui n'utilise pas les programmes ne les requête pas.
 */
class SiteContexte
{
    private ?Parametre $parametres = null;

    /** @var array<string, mixed> */
    private array $cache = [];

    public function __construct(
        private readonly ParametreRepository $parametreRepository,
        private readonly ProgrammeRepository $programmeRepository,
        private readonly ThematiqueRepository $thematiqueRepository,
        private readonly TypeArticleRepository $typeArticleRepository,
        private readonly TypePublicationRepository $typePublicationRepository,
    ) {
    }

    public function getParametres(): Parametre
    {
        return $this->parametres ??= $this->parametreRepository->obtenir();
    }

    /** @return \App\Entity\Programme[] */
    public function getProgrammes(): array
    {
        return $this->cache['programmes'] ??= $this->programmeRepository->trouverActifs();
    }

    /** @return \App\Entity\Thematique[] */
    public function getThematiques(): array
    {
        return $this->cache['thematiques'] ??= $this->thematiqueRepository->trouverOrdonnees();
    }

    /** @return \App\Entity\TypeArticle[] */
    public function getTypesArticle(): array
    {
        return $this->cache['typesArticle'] ??= $this->typeArticleRepository->trouverOrdonnes();
    }

    /** @return \App\Entity\TypePublication[] */
    public function getTypesPublication(): array
    {
        return $this->cache['typesPublication'] ??= $this->typePublicationRepository->trouverOrdonnes();
    }

    /** Année courante, utilisée dans le copyright du pied de page. */
    public function getAnnee(): string
    {
        return date('Y');
    }
}
