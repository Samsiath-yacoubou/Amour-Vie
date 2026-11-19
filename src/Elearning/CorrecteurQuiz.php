<?php

declare(strict_types=1);

namespace App\Elearning;

use App\Entity\Quiz;

/**
 * Correction côté serveur d'un quiz de module.
 *
 * Contrairement aux quiz publics (corrigés dans le navigateur), les bonnes
 * réponses ne quittent le serveur qu'après la soumission, et le score sert
 * à valider le module.
 */
final class CorrecteurQuiz
{
    /**
     * Questions telles qu'envoyées au navigateur : sans les bonnes réponses.
     *
     * @return list<array{id: int, enonce: string, choix: list<array{id: int, libelle: string}>}>
     */
    public function questionsSansReponses(Quiz $quiz): array
    {
        $questions = [];
        foreach ($quiz->getQuestions() as $question) {
            $choix = [];
            foreach ($question->getChoix() as $unChoix) {
                $choix[] = ['id' => $unChoix->getId(), 'libelle' => (string) $unChoix->getLibelle()];
            }

            $questions[] = ['id' => $question->getId(), 'enonce' => (string) $question->getEnonce(), 'choix' => $choix];
        }

        return $questions;
    }

    /**
     * @param array<int|string, mixed> $reponses identifiant de question → identifiant du choix retenu
     *
     * @return array{score: int, total: int, pourcentage: int, reussi: bool, seuil: int, corrections: array<int, array{choixCorrects: list<int>, juste: bool, explication: ?string}>}
     */
    public function corriger(Quiz $quiz, array $reponses, int $seuil): array
    {
        $score = 0;
        $total = 0;
        $corrections = [];

        foreach ($quiz->getQuestions() as $question) {
            ++$total;

            $choixCorrects = [];
            foreach ($question->getChoix() as $choix) {
                if ($choix->isEstCorrect()) {
                    $choixCorrects[] = $choix->getId();
                }
            }

            $repondu = $reponses[$question->getId()] ?? null;
            $juste = is_numeric($repondu) && \in_array((int) $repondu, $choixCorrects, true);
            if ($juste) {
                ++$score;
            }

            $corrections[$question->getId()] = [
                'choixCorrects' => $choixCorrects,
                'juste' => $juste,
                'explication' => $question->getExplication(),
            ];
        }

        $pourcentage = $total > 0 ? (int) floor($score * 100 / $total) : 0;

        return [
            'score' => $score,
            'total' => $total,
            'pourcentage' => $pourcentage,
            'reussi' => $total > 0 && $pourcentage >= $seuil,
            'seuil' => $seuil,
            'corrections' => $corrections,
        ];
    }
}
