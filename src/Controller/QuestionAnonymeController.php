<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\QuestionAnonyme;
use App\Form\QuestionAnonymeType;
use App\Repository\QuestionAnonymeRepository;
use App\Repository\ThematiqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * « Espace jeunes : pose ta question » et la FAQ publique qui en découle.
 * Anonymat total : aucun champ e-mail, pas de suivi individuel.
 */
class QuestionAnonymeController extends AbstractController
{
    /** Nombre maximal de questions acceptées par IP sur une heure glissante. */
    private const LIMITE_PAR_HEURE = 5;

    #[Route('/espace-jeunes/pose-ta-question', name: 'question_anonyme_poser', methods: ['GET', 'POST'])]
    public function poser(
        Request $request,
        EntityManagerInterface $entityManager,
        QuestionAnonymeRepository $questionAnonymeRepository,
    ): Response {
        $question = new QuestionAnonyme();
        $formulaire = $this->createForm(QuestionAnonymeType::class, $question);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $ip = $request->getClientIp();

            // Le pot de miel n'est jamais rempli par un visiteur légitime :
            // on répond comme si tout allait bien pour ne pas informer le robot.
            $estSpam = '' !== trim((string) $formulaire->get('siteWeb')->getData());

            $tropDeMessages = null !== $ip && $questionAnonymeRepository->compterDepuisIp(
                $ip,
                new \DateTimeImmutable('-1 hour'),
            ) >= self::LIMITE_PAR_HEURE;

            if ($tropDeMessages) {
                $this->addFlash('warning', 'Merci de patienter avant de poser une nouvelle question.');

                return $this->redirectToRoute('question_anonyme_poser');
            }

            if (!$estSpam) {
                $question->setAdresseIp($ip);
                $entityManager->persist($question);
                $entityManager->flush();
            }

            $this->addFlash('success', 'Ta question a bien été envoyée. Un·e pair-éducateur·rice va te répondre.');

            return $this->redirectToRoute('question_anonyme_poser');
        }

        return $this->render('question_anonyme/poser.html.twig', [
            'formulaire' => $formulaire,
        ]);
    }

    #[Route('/ressources/faq', name: 'faq', methods: ['GET'])]
    public function faq(
        Request $request,
        QuestionAnonymeRepository $questionAnonymeRepository,
        ThematiqueRepository $thematiqueRepository,
    ): Response {
        $thematique = $request->query->get('thematique');

        return $this->render('question_anonyme/faq.html.twig', [
            'questions' => $questionAnonymeRepository->trouverFaqPublique($thematique),
            'thematiques' => $thematiqueRepository->trouverOrdonnees(),
            'thematiqueActive' => $thematique,
        ]);
    }
}
