<?php

declare(strict_types=1);

namespace App\Elearning;

use App\Entity\Apprenant;
use App\Entity\Cours;
use App\Entity\InscriptionCours;
use App\Entity\Lecon;
use App\Repository\LeconTermineeRepository;
use App\Repository\TentativeExamenRepository;
use App\Repository\TentativeQuizRepository;

/**
 * Calcule où en est un apprenant dans un cours.
 *
 * Règle de validation : un module est validé quand toutes ses leçons sont
 * lues et, s'il a un quiz, quand ce quiz a été réussi au moins une fois
 * (score ≥ seuil du cours). Le cours est terminé quand tous ses modules
 * sont validés et, s'il a un examen final, quand celui-ci est réussi
 * (dans la limite du nombre de tentatives). Un cours terminé ouvre droit
 * au certificat.
 */
final class ProgressionCours
{
    public function __construct(
        private readonly LeconTermineeRepository $leconTermineeRepository,
        private readonly TentativeQuizRepository $tentativeQuizRepository,
        private readonly TentativeExamenRepository $tentativeExamenRepository,
    ) {
    }

    public function calculer(Apprenant $apprenant, Cours $cours): BilanCours
    {
        $leconsLues = $this->leconTermineeRepository->idsTerminees($apprenant, $cours);
        $quizParModule = $this->tentativeQuizRepository->bilanParModule($apprenant, $cours);

        $modules = [];
        $etapesFaites = 0;
        $etapesTotal = 0;
        $prochaineEtape = null;

        foreach ($cours->getModules() as $module) {
            $lecons = [];
            $toutesLues = true;

            foreach ($module->getLecons() as $lecon) {
                $lue = isset($leconsLues[$lecon->getId()]);
                $lecons[] = ['lecon' => $lecon, 'terminee' => $lue];

                ++$etapesTotal;
                if ($lue) {
                    ++$etapesFaites;
                } else {
                    $toutesLues = false;
                    $prochaineEtape ??= ['type' => 'lecon', 'lecon' => $lecon, 'module' => $module];
                }
            }

            $quiz = null;
            $quizReussi = true;
            if ($module->aUnQuiz()) {
                $quiz = $quizParModule[$module->getId()] ?? ['meilleur' => null, 'reussi' => false];
                $quizReussi = $quiz['reussi'];

                ++$etapesTotal;
                if ($quizReussi) {
                    ++$etapesFaites;
                } elseif ($toutesLues) {
                    // On propose le quiz une fois les leçons du module lues.
                    $prochaineEtape ??= ['type' => 'quiz', 'module' => $module];
                }
            }

            $modules[] = [
                'module' => $module,
                'lecons' => $lecons,
                'quiz' => $quiz,
                'valide' => $toutesLues && $quizReussi,
            ];
        }

        // Toutes les leçons lues mais un quiz en retard dans un module précédent.
        if (null === $prochaineEtape) {
            foreach ($modules as $etat) {
                if (!$etat['valide']) {
                    $prochaineEtape = ['type' => 'quiz', 'module' => $etat['module']];
                    break;
                }
            }
        }

        // Examen final : dernière étape, ouverte une fois tous les modules validés.
        $examen = null;
        if ($cours->aUnExamenFinal()) {
            $resultats = $this->tentativeExamenRepository->bilan($apprenant, $cours);
            $max = $cours->getTentativesExamenMax();
            $restantes = $max > 0 ? max(0, $max - $resultats['tentatives']) : null;
            $modulesValides = [] === array_filter($modules, static fn (array $etat) => !$etat['valide']);

            $examen = $resultats + [
                'max' => $max,
                'restantes' => $restantes,
                'accessible' => $modulesValides && !$resultats['reussi'] && 0 !== $restantes,
                'epuise' => !$resultats['reussi'] && 0 === $restantes,
            ];

            ++$etapesTotal;
            if ($resultats['reussi']) {
                ++$etapesFaites;
            } elseif (null === $prochaineEtape && $examen['accessible']) {
                $prochaineEtape = ['type' => 'examen'];
            }
        }

        return new BilanCours($modules, $etapesFaites, $etapesTotal, $prochaineEtape, $examen);
    }

    /**
     * Met à jour la date de fin de l'inscription si le cours vient d'être
     * terminé. Renvoie true au moment où le cours devient terminé.
     */
    public function actualiserInscription(InscriptionCours $inscription): bool
    {
        if ($inscription->isTerminee()) {
            return false;
        }

        $bilan = $this->calculer($inscription->getApprenant(), $inscription->getCours());
        if (!$bilan->estTermine()) {
            return false;
        }

        $inscription->setTermineeAt(new \DateTimeImmutable());

        return true;
    }

    /**
     * Leçons précédente et suivante dans l'ordre de lecture du cours.
     *
     * @return array{precedente: ?Lecon, suivante: ?Lecon}
     */
    public function voisines(Lecon $lecon): array
    {
        $lecons = $lecon->getCours()->getLecons();
        $position = array_search($lecon, $lecons, true);

        return [
            'precedente' => false !== $position && $position > 0 ? $lecons[$position - 1] : null,
            'suivante' => false !== $position ? ($lecons[$position + 1] ?? null) : null,
        ];
    }
}
