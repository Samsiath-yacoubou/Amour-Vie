<?php

declare(strict_types=1);

namespace App\Elearning;

use App\Entity\Lecon;
use App\Entity\ModuleCours;

/**
 * État d'avancement d'un apprenant dans un cours (cf. ProgressionCours).
 */
final class BilanCours
{
    /**
     * @param list<array{module: ModuleCours, lecons: list<array{lecon: Lecon, terminee: bool}>, quiz: ?array{meilleur: ?int, reussi: bool}, valide: bool}> $modules
     * @param array{type: 'lecon'|'quiz'|'examen', module?: ModuleCours, lecon?: Lecon}|null                                                             $prochaineEtape
     * @param array{tentatives: int, meilleur: ?int, reussi: bool, max: int, restantes: ?int, accessible: bool, epuise: bool}|null                         $examen         null si le cours n'a pas d'examen final
     */
    public function __construct(
        public readonly array $modules,
        public readonly int $etapesFaites,
        public readonly int $etapesTotal,
        public readonly ?array $prochaineEtape,
        public readonly ?array $examen = null,
    ) {
    }

    public function getPourcentage(): int
    {
        return $this->etapesTotal > 0 ? (int) floor($this->etapesFaites * 100 / $this->etapesTotal) : 0;
    }

    public function estTermine(): bool
    {
        return $this->etapesTotal > 0 && $this->etapesFaites === $this->etapesTotal;
    }

    public function estCommence(): bool
    {
        return $this->etapesFaites > 0;
    }

    public function leconEstTerminee(Lecon $lecon): bool
    {
        foreach ($this->modules as $etat) {
            foreach ($etat['lecons'] as $ligne) {
                if ($ligne['lecon'] === $lecon) {
                    return $ligne['terminee'];
                }
            }
        }

        return false;
    }
}
