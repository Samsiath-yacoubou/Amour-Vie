<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Utilisateur;
use App\Enum\RoleUtilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Crée un compte d'accès au back-office My Amour&Vie.
 *
 * Indispensable à la mise en production : les fixtures de démonstration ne
 * doivent jamais être chargées sur l'environnement réel.
 */
#[AsCommand(
    name: 'app:creer-utilisateur',
    description: 'Crée un compte pour le back-office My Amour&Vie.',
)]
class CreerUtilisateurCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UtilisateurRepository $utilisateurRepository,
        private readonly UserPasswordHasherInterface $hacheur,
        private readonly ValidatorInterface $validateur,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Adresse e-mail, qui sert d\'identifiant')
            ->addArgument('prenom', InputArgument::OPTIONAL, 'Prénom')
            ->addArgument('nom', InputArgument::OPTIONAL, 'Nom')
            ->addOption('role', 'r', InputOption::VALUE_REQUIRED, 'Rôle : super-admin, editeur, gestionnaire ou contributeur', 'super-admin')
            ->addOption('mot-de-passe', 'p', InputOption::VALUE_REQUIRED, 'Mot de passe (sinon demandé de façon masquée)')
            ->setHelp(<<<'AIDE'
                Crée un compte d'accès au back-office.

                  <info>php bin/console app:creer-utilisateur</info>
                  <info>php bin/console app:creer-utilisateur contact@amouretvie.bj Kossi Adjovi --role=super-admin</info>

                Rôles disponibles :
                  <comment>super-admin</comment>    accès complet
                  <comment>editeur</comment>        rédige et publie les articles
                  <comment>gestionnaire</comment>   gère les contenus institutionnels
                  <comment>contributeur</comment>   rédige sans pouvoir publier
                AIDE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Création d\'un compte My Amour&Vie');

        $email = $input->getArgument('email') ?? $io->ask('Adresse e-mail');
        $prenom = $input->getArgument('prenom') ?? $io->ask('Prénom');
        $nom = $input->getArgument('nom') ?? $io->ask('Nom');

        $roles = [
            'super-admin' => RoleUtilisateur::SUPER_ADMIN,
            'editeur' => RoleUtilisateur::EDITEUR,
            'gestionnaire' => RoleUtilisateur::GESTIONNAIRE,
            'contributeur' => RoleUtilisateur::CONTRIBUTEUR,
        ];

        $cleRole = $input->getOption('role');

        if (!isset($roles[$cleRole])) {
            $io->error(sprintf('Rôle « %s » inconnu. Valeurs acceptées : %s.', $cleRole, implode(', ', array_keys($roles))));

            return Command::INVALID;
        }

        if (null !== $this->utilisateurRepository->findOneBy(['email' => $email])) {
            $io->error(sprintf('Un compte existe déjà avec l\'adresse « %s ».', $email));

            return Command::FAILURE;
        }

        $motDePasse = $input->getOption('mot-de-passe');

        if (null === $motDePasse) {
            $question = (new Question('Mot de passe (la saisie reste masquée) : '))
                ->setHidden(true)
                ->setHiddenFallback(false);

            $motDePasse = $io->askQuestion($question);

            $confirmation = (new Question('Confirmez le mot de passe : '))
                ->setHidden(true)
                ->setHiddenFallback(false);

            if ($motDePasse !== $io->askQuestion($confirmation)) {
                $io->error('Les deux saisies ne correspondent pas.');

                return Command::FAILURE;
            }
        }

        if (null === $motDePasse || mb_strlen($motDePasse) < 12) {
            $io->error('Le mot de passe doit contenir au moins 12 caractères.');

            return Command::FAILURE;
        }

        $utilisateur = new Utilisateur();
        $utilisateur
            ->setEmail((string) $email)
            ->setPrenom((string) $prenom)
            ->setNom((string) $nom)
            ->setRoles([$roles[$cleRole]->value])
            ->setActif(true)
            ->setPassword($this->hacheur->hashPassword($utilisateur, $motDePasse));

        $erreurs = $this->validateur->validate($utilisateur);

        if (\count($erreurs) > 0) {
            $io->error('Le compte n\'a pas pu être créé :');
            foreach ($erreurs as $erreur) {
                $io->writeln(sprintf('  • %s : %s', $erreur->getPropertyPath(), $erreur->getMessage()));
            }

            return Command::FAILURE;
        }

        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        $io->success(sprintf(
            'Compte créé : %s (%s), rôle %s.',
            $utilisateur->getNomComplet(),
            $utilisateur->getEmail(),
            $roles[$cleRole]->libelle(),
        ));

        $io->note('Connexion au back-office : /mastering');

        return Command::SUCCESS;
    }
}
