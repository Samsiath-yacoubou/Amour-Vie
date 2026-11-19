<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Cours;
use App\Entity\Lecon;
use App\Entity\ModuleCours;
use App\Repository\CoursRepository;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Crée le cours pilote de l'e-learning (phase 1), sans rien effacer.
 *
 * Le contenu est un exemple de démonstration : il doit être relu, complété
 * ou remplacé par l'ABMS avant toute publication. Le cours est donc créé
 * non publié, sauf option --publier (environnement de test).
 */
#[AsCommand(name: 'app:elearning:cours-pilote', description: 'Crée le cours pilote de démonstration de l\'e-learning.')]
class CoursPiloteCommand extends Command
{
    private const TITRE = 'Contraception : les bases (cours pilote)';

    /** Quiz pédagogique existant réutilisé pour valider le module 1, s'il est présent. */
    private const SLUG_QUIZ_MODULE_1 = 'teste-tes-connaissances-sur-la-contraception';

    /** Quiz existant réutilisé comme examen final du cours, s'il est présent. */
    private const SLUG_EXAMEN_FINAL = 'vih-ist-vrai-ou-faux';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CoursRepository $coursRepository,
        private readonly QuizRepository $quizRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('publier', null, InputOption::VALUE_NONE, 'Publie le cours (à réserver aux tests : le contenu doit être validé par l\'ABMS).');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (null !== $this->coursRepository->findOneBy(['titre' => self::TITRE])) {
            $io->note('Le cours pilote existe déjà : rien à faire.');

            return Command::SUCCESS;
        }

        $cours = (new Cours())
            ->setTitre(self::TITRE)
            ->setResume('Comprendre à quoi sert la contraception, pourquoi le préservatif protège deux fois, et où trouver conseil près de chez toi.')
            ->setDescription('<p><em>Contenu de démonstration, à valider par l\'ABMS avant publication.</em></p>'
                .'<p>Ce cours court t\'aide à faire des choix éclairés pour ta santé. Il se suit en une vingtaine de minutes, depuis ton téléphone.</p>')
            ->setDureeMinutes(20)
            ->setNiveau(Cours::NIVEAU_DEBUTANT)
            ->setSeuilReussite(70)
            ->setExamenFinal($this->quizRepository->findOneBy(['slug' => self::SLUG_EXAMEN_FINAL]))
            ->setTentativesExamenMax(3)
            ->setPublie((bool) $input->getOption('publier'));

        $module1 = (new ModuleCours())
            ->setTitre('Comprendre la contraception')
            ->setDescription('Ce qu\'est la contraception et pourquoi le préservatif est unique.')
            ->setOrdre(1)
            ->setQuiz($this->quizRepository->findOneBy(['slug' => self::SLUG_QUIZ_MODULE_1]));

        $module1->addLecon((new Lecon())
            ->setTitre('Qu\'est-ce que la contraception ?')
            ->setDureeMinutes(5)
            ->setOrdre(1)
            ->setContenu(
                '<p>La <strong>contraception</strong>, c\'est l\'ensemble des moyens qui permettent d\'avoir des relations sexuelles <strong>sans grossesse non désirée</strong>.</p>'
                .'<p>Il existe plusieurs méthodes : le préservatif (masculin ou féminin), la pilule, l\'implant, l\'injection, le stérilet (DIU)… Chacune a ses avantages, et la bonne méthode dépend de chaque personne.</p>'
                .'<p>Pour choisir, le mieux est d\'en parler avec un·e professionnel·le de santé ou un·e conseiller·ère d\'un Centre Jeunes : c\'est gratuit et confidentiel.</p>'
                .'<p><strong>À retenir :</strong> utiliser une contraception, c\'est prendre soin de soi et de son avenir.</p>'
            ));

        $module1->addLecon((new Lecon())
            ->setTitre('Le préservatif, la double protection')
            ->setDureeMinutes(5)
            ->setOrdre(2)
            ->setContenu(
                '<p>Le <strong>préservatif</strong> est la seule méthode qui protège à la fois :</p>'
                .'<ul><li>contre une <strong>grossesse non désirée</strong> ;</li><li>contre les <strong>infections sexuellement transmissibles</strong> (IST), dont le VIH.</li></ul>'
                .'<p>Pour qu\'il protège bien : un préservatif neuf à chaque rapport, mis dès le début, vérifier la date de péremption, ne jamais en utiliser deux l\'un sur l\'autre.</p>'
                .'<p>Les autres méthodes (pilule, implant…) évitent la grossesse mais <strong>ne protègent pas des IST</strong>. On peut les associer au préservatif.</p>'
            ));

        $module2 = (new ModuleCours())
            ->setTitre('Trouver de l\'aide près de chez toi')
            ->setDescription('Les lieux et contacts pour poser tes questions en toute confiance.')
            ->setOrdre(2);

        $module2->addLecon((new Lecon())
            ->setTitre('Où s\'informer et se faire conseiller ?')
            ->setDureeMinutes(5)
            ->setOrdre(1)
            ->setContenu(
                '<p>Tu as une question, un doute, besoin d\'un conseil ? Tu n\'es pas seul·e.</p>'
                .'<ul><li>Les <strong>Centres Jeunes Amour&amp;Vie</strong> t\'accueillent gratuitement et en toute confidentialité, partout au Bénin.</li>'
                .'<li>La <strong>ligne verte 7344</strong> répond à tes questions par téléphone.</li>'
                .'<li>Sur le site, la rubrique <strong>« Pose ta question »</strong> te permet d\'écrire de façon anonyme à un·e pair-éducateur·rice.</li></ul>'
                .'<p>En cas de rapport non protégé, une <strong>contraception d\'urgence</strong> existe : elle est d\'autant plus efficace qu\'elle est prise tôt. Rends-toi rapidement dans un centre de santé ou une pharmacie.</p>'
            ));

        $cours->addModule($module1)->addModule($module2);
        $this->entityManager->persist($cours);
        $this->entityManager->flush();

        $io->success(sprintf('Cours pilote créé (%s), avec %d modules.', $cours->isPublie() ? 'publié' : 'non publié', $cours->getModules()->count()));
        if (null === $module1->getQuiz()) {
            $io->warning('Quiz « '.self::SLUG_QUIZ_MODULE_1.' » introuvable : le module 1 n\'a pas de quiz.');
        }

        return Command::SUCCESS;
    }
}
