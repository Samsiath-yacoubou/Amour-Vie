<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\QuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Quiz pédagogiques. Le score est calculé côté client : rien n'est envoyé
 * au serveur, aucune tentative n'est enregistrée.
 */
class QuizController extends AbstractController
{
    #[Route('/quiz', name: 'quiz_liste', methods: ['GET'])]
    public function liste(QuizRepository $quizRepository): Response
    {
        return $this->render('quiz/liste.html.twig', [
            'quiz' => $quizRepository->trouverActifs(),
        ]);
    }

    #[Route('/quiz/{slug}', name: 'quiz_detail', methods: ['GET'])]
    public function detail(string $slug, QuizRepository $quizRepository): Response
    {
        $quiz = $quizRepository->trouverActifParSlug($slug);

        if (null === $quiz) {
            throw $this->createNotFoundException('Ce quiz n\'existe pas.');
        }

        // Données de l'îlot Vue : array_values() garantit des tableaux JSON
        // même si les clés des collections Doctrine ne sont pas contiguës.
        $questions = array_values(array_map(
            static fn ($question) => [
                'enonce' => $question->getEnonce(),
                'explication' => $question->getExplication(),
                'choix' => array_values(array_map(
                    static fn ($choix) => [
                        'libelle' => $choix->getLibelle(),
                        'correct' => $choix->isEstCorrect(),
                    ],
                    $question->getChoix()->toArray()
                )),
            ],
            $quiz->getQuestions()->toArray()
        ));

        return $this->render('quiz/detail.html.twig', [
            'quiz' => $quiz,
            'questionsQuiz' => $questions,
        ]);
    }
}
