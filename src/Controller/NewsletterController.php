<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\NewsletterAbonne;
use App\Form\NewsletterType;
use App\Repository\NewsletterAbonneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Inscription à la newsletter, depuis le formulaire du pied de page.
 * Collecte uniquement : l'envoi de campagnes n'est pas encore branché.
 */
class NewsletterController extends AbstractController
{
    #[Route('/newsletter/inscription', name: 'newsletter_inscrire', methods: ['POST'])]
    public function inscrire(
        Request $request,
        EntityManagerInterface $entityManager,
        NewsletterAbonneRepository $newsletterAbonneRepository,
    ): Response {
        $abonne = new NewsletterAbonne();
        $formulaire = $this->createForm(NewsletterType::class, $abonne);
        $formulaire->handleRequest($request);

        $destination = $request->headers->get('referer') ?: $this->generateUrl('accueil');

        if (!$formulaire->isSubmitted() || !$formulaire->isValid()) {
            $this->addFlash('warning', 'Adresse e-mail invalide, merci de réessayer.');

            return $this->redirect($destination);
        }

        // Le pot de miel n'est jamais rempli par un visiteur légitime :
        // on répond comme si tout allait bien pour ne pas informer le robot.
        $estSpam = '' !== trim((string) $formulaire->get('siteWeb')->getData());

        if (!$estSpam) {
            if ($newsletterAbonneRepository->estDejaAbonne($abonne->getEmail())) {
                $this->addFlash('success', 'Tu es déjà inscrit·e à la newsletter !');

                return $this->redirect($destination);
            }

            $abonne->setAdresseIp($request->getClientIp());
            $entityManager->persist($abonne);
            $entityManager->flush();
        }

        $this->addFlash('success', 'Merci, ton inscription à la newsletter est confirmée !');

        return $this->redirect($destination);
    }
}
