<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Authentification au back-office My Amour&Vie.
 */
class SecuriteController extends AbstractController
{
    #[Route('/mastering/connexion', name: 'connexion', methods: ['GET', 'POST'])]
    public function connexion(AuthenticationUtils $authenticationUtils): Response
    {
        // Un utilisateur déjà connecté n'a rien à faire sur l'écran de connexion.
        if ($this->getUser()) {
            return $this->redirectToRoute('admin');
        }

        return $this->render('securite/connexion.html.twig', [
            'derniereSaisie' => $authenticationUtils->getLastUsername(),
            'erreur' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/mastering/deconnexion', name: 'deconnexion', methods: ['GET'])]
    public function deconnexion(): never
    {
        // Interceptée par le pare-feu Symfony : ce corps n'est jamais exécuté.
        throw new \LogicException('La déconnexion est gérée par le pare-feu de sécurité.');
    }
}
