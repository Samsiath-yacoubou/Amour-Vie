<?php

declare(strict_types=1);

namespace App\Command;

use App\Elearning\ProgressionCours;
use App\Entity\InscriptionCours;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Rappel par e-mail d'un cours resté inachevé (option du cahier des charges).
 *
 * À planifier une fois par jour (tâche cron). Un seul rappel par période
 * d'inactivité : si le jeune reprend le cours puis s'arrête de nouveau, il
 * pourra recevoir un nouveau rappel.
 *
 *     php bin/console app:elearning:rappels --simulation   # liste sans envoyer
 *     php bin/console app:elearning:rappels                 # envoie
 */
#[AsCommand(name: 'app:elearning:rappels', description: 'Envoie un rappel aux apprenants dont un cours est inachevé depuis plusieurs jours.')]
class RappelsElearningCommand extends Command
{
    private const JOURS_INACTIVITE = 7;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProgressionCours $progression,
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        #[Autowire('%env(MAILER_EXPEDITEUR)%')]
        private readonly string $expediteur,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('simulation', null, InputOption::VALUE_NONE, 'Affiche les rappels qui seraient envoyés, sans rien envoyer.')
            ->addOption('jours', null, InputOption::VALUE_REQUIRED, 'Nombre de jours d\'inactivité avant rappel.', (string) self::JOURS_INACTIVITE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $simulation = (bool) $input->getOption('simulation');
        $limite = new \DateTimeImmutable(sprintf('-%d days', max(1, (int) $input->getOption('jours'))));

        /** @var InscriptionCours[] $inscriptions */
        $inscriptions = $this->entityManager->createQueryBuilder()
            ->select('i', 'a', 'c')
            ->from(InscriptionCours::class, 'i')
            ->join('i.apprenant', 'a')
            ->join('i.cours', 'c')
            ->andWhere('i.termineeAt IS NULL')
            ->andWhere('a.actif = true')
            ->andWhere('c.publie = true')
            // Dernière activité connue : mise à jour de l'inscription, sinon sa création.
            ->andWhere('COALESCE(i.updatedAt, i.createdAt) < :limite')
            // Pas de rappel déjà envoyé depuis cette dernière activité.
            ->andWhere('i.rappelEnvoyeAt IS NULL OR i.rappelEnvoyeAt < COALESCE(i.updatedAt, i.createdAt)')
            ->setParameter('limite', $limite)
            ->getQuery()
            ->getResult();

        $envoyes = 0;
        foreach ($inscriptions as $inscription) {
            $apprenant = $inscription->getApprenant();
            $cours = $inscription->getCours();
            $bilan = $this->progression->calculer($apprenant, $cours);

            // Examen épuisé : il n'y a plus rien à reprendre, inutile de relancer.
            if (null !== $bilan->examen && $bilan->examen['epuise']) {
                continue;
            }

            $io->writeln(sprintf(' - %s <%s> : « %s » (%d %%)', $apprenant->getNomComplet(), $apprenant->getEmail(), $cours->getTitre(), $bilan->getPourcentage()));
            if ($simulation) {
                continue;
            }

            try {
                $this->mailer->send((new TemplatedEmail())
                    ->from(Address::create($this->expediteur))
                    ->to(new Address((string) $apprenant->getEmail(), $apprenant->getNomComplet()))
                    ->subject(sprintf('Ton cours « %s » t\'attend', $cours->getTitre()))
                    ->htmlTemplate('apprendre/email/rappel.html.twig')
                    ->context([
                        'apprenant' => $apprenant,
                        'cours' => $cours,
                        'pourcentage' => $bilan->getPourcentage(),
                        'lien' => $this->urlGenerator->generate('apprendre_cours', ['slug' => $cours->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                    ]));
            } catch (TransportExceptionInterface $e) {
                $io->warning(sprintf('Envoi impossible à %s : %s', $apprenant->getEmail(), $e->getMessage()));
                continue;
            }

            // Écriture directe : ne pas toucher updatedAt (qui marque l'activité du jeune).
            $this->entityManager->getConnection()->update(
                'inscription_cours',
                ['rappel_envoye_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')],
                ['id' => $inscription->getId()],
            );
            ++$envoyes;
        }

        $io->success($simulation
            ? sprintf('%d rappel(s) seraient envoyés (simulation, rien n\'a été envoyé).', \count($inscriptions))
            : sprintf('%d rappel(s) envoyé(s).', $envoyes));

        return Command::SUCCESS;
    }
}
