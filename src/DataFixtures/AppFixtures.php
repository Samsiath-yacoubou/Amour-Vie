<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\CentreJeune;
use App\Entity\ChiffreCle;
use App\Entity\Emission;
use App\Entity\Evenement;
use App\Entity\Membre;
use App\Entity\NewsletterAbonne;
use App\Entity\Parametre;
use App\Entity\Partenaire;
use App\Entity\Programme;
use App\Entity\Publication;
use App\Entity\QuestionAnonyme;
use App\Entity\Quiz;
use App\Entity\QuizChoix;
use App\Entity\QuizQuestion;
use App\Entity\SectionPage;
use App\Entity\Temoignage;
use App\Entity\Thematique;
use App\Entity\TypeArticle;
use App\Entity\TypePublication;
use App\Entity\Utilisateur;
use App\Entity\Valeur;
use App\Enum\CategorieMembre;
use App\Enum\RoleUtilisateur;
use App\Enum\StatutArticle;
use App\Enum\StatutModeration;
use App\Enum\StatutQuestion;
use App\Enum\TypeEmission;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Jeu de démonstration destiné à la recette (§ 6.1).
 *
 * Les textes sont réalistes mais provisoires : ils doivent être remplacés par
 * les contenus validés par Amour&Vie ABMS avant la mise en production.
 */
class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hacheur)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $utilisateurs = $this->chargerUtilisateurs($manager);
        $this->chargerParametres($manager);
        $programmes = $this->chargerProgrammes($manager);
        $thematiques = $this->chargerThematiques($manager);
        $typesArticle = $this->chargerTypesArticle($manager);
        $typesPublication = $this->chargerTypesPublication($manager);
        $this->chargerValeurs($manager);
        $this->chargerChiffresCles($manager);
        $this->chargerSectionsDePage($manager);
        $this->chargerEquipe($manager);
        $this->chargerPartenaires($manager);
        $this->chargerArticles($manager, $programmes, $thematiques, $typesArticle, $utilisateurs);
        $this->chargerPublications($manager, $programmes, $thematiques, $typesPublication);
        $this->chargerEvenements($manager, $programmes);
        $this->chargerCentresJeunes($manager, $programmes, $thematiques);
        $this->chargerEmissions($manager, $programmes, $thematiques);
        $this->chargerTemoignages($manager);
        $this->chargerQuestionsAnonymes($manager, $thematiques);
        $this->chargerNewsletterAbonnes($manager);
        $this->chargerQuiz($manager, $thematiques);

        $manager->flush();
    }

    /** @return array<string, Utilisateur> */
    private function chargerUtilisateurs(ObjectManager $manager): array
    {
        $comptes = [
            'admin' => ['Adjovi', 'Kossi', 'admin@amouretvie.bj', RoleUtilisateur::SUPER_ADMIN, "Coordonnateur·rice national·e du programme"],
            'editeur' => ['Sossou', 'Bernadette', 'editeur@amouretvie.bj', RoleUtilisateur::EDITEUR, "Chargée de communication"],
            'gestionnaire' => ['Dossou', 'Marius', 'gestionnaire@amouretvie.bj', RoleUtilisateur::GESTIONNAIRE, "Responsable des Centres Jeunes"],
            'contributeur' => ['Aholou', 'Fatima', 'contributeur@amouretvie.bj', RoleUtilisateur::CONTRIBUTEUR, "Pair-éducatrice / Assistante de programme"],
        ];

        $utilisateurs = [];

        foreach ($comptes as $cle => [$nom, $prenom, $email, $role, $fonction]) {
            $utilisateur = new Utilisateur();
            $utilisateur
                ->setNom($nom)
                ->setPrenom($prenom)
                ->setEmail($email)
                ->setFonction($fonction)
                ->setRoles([$role->value])
                ->setActif(true);

            // Mot de passe de démonstration : à changer impérativement en production.
            $utilisateur->setPassword($this->hacheur->hashPassword($utilisateur, 'AmourEtVie2026!'));

            $manager->persist($utilisateur);
            $utilisateurs[$cle] = $utilisateur;
        }

        return $utilisateurs;
    }

    private function chargerParametres(ObjectManager $manager): void
    {
        $parametre = new Parametre();
        $parametre
            ->setNomSite('Amour&Vie ABMS')
            ->setSlogan('Programme des jeunes animé par les jeunes et pour les jeunes')
            ->setHeroTitre('Les jeunes au coeur du changement')
            ->setHeroSousTitre(
                "Amour&Vie accompagne les jeunes dans leur engagement, leur santé et leurs droits "
                ."pour leur permettre de construire leur avenir et de faire entendre leur voix."
            )
            ->setHeroBouton1Libelle('Découvrir Amour&Vie')
            ->setHeroBouton1Lien('/qui-sommes-nous')
            ->setHeroBouton2Libelle('Nos actions')
            ->setHeroBouton2Lien('/programmes')
            ->setAdresse("Amour&Vie ABMS\n08 BP 0876 Tri postal Cotonou, République du Bénin")
            ->setTelephone('7344')
            ->setTelephoneSecondaire('+229 69 11 77 77')
            ->setEmail('amouretvieabms@abmsbj.org')
            ->setHoraires('Du lundi au vendredi, de 8h00 à 17h00')
            // URLs de réseaux sociaux volontairement génériques : à remplacer par les comptes réels
            // (page Facebook « Amouretvie Abms », compte Instagram « abmsbenin ») avant mise en production.
            ->setFacebook('https://www.facebook.com/')
            ->setInstagram('https://www.instagram.com/')
            ->setLinkedin('https://www.linkedin.com/')
            ->setTwitter('https://x.com/')
            ->setYoutube('https://www.youtube.com/')
            ->setTiktok('https://www.tiktok.com/')
            ->setFooterTexte(
                "Amour&Vie informe, accompagne et mobilise les jeunes pour leur santé et leurs droits sexuels et reproductifs."
            )
            ->setCopyright('© '.date('Y').' Amour&Vie ABMS. Tous droits réservés.')
            ->setSeoTitre('Amour&Vie ABMS · Programme des jeunes')
            ->setSeoDescription(
                "Amour&Vie ABMS est un programme animé par les jeunes et pour les jeunes au Bénin, engagé pour la santé et les droits sexuels et reproductifs."
            )
            ->setSeoMotsCles('Amour&Vie, ABMS, jeunes, santé sexuelle, droits sexuels et reproductifs, Bénin')
            ->setMentionsLegales($this->texteMentionsLegales())
            ->setPolitiqueConfidentialite($this->textePolitiqueConfidentialite())
            ->setConditionsUtilisation($this->texteConditionsUtilisation());

        $manager->persist($parametre);
    }

    /** @return array<string, Programme> */
    private function chargerProgrammes(ObjectManager $manager): array
    {
        // Les 5 piliers d'action du programme Amour&Vie ("Nos actions").
        $definitions = [
            'CJAV' => [
                'nom' => 'Centres Jeunes Amour&Vie',
                'couleur' => '#1e9e6d',
                'court' => "Un accueil confidentiel et des services de santé sexuelle et reproductive pensés par et pour les jeunes.",
                'description' => "<p>Les Centres Jeunes Amour&Vie (CJAV) sont des espaces physiques d'accueil où les jeunes de 15 à 24 ans peuvent, gratuitement et en toute confidentialité, s'informer, être écoutés et accéder à des services de santé sexuelle et reproductive adaptés à leur âge.</p><p>Chaque centre propose des activités récréatives et éducatives en plus des services cliniques, dans un cadre bienveillant et sans jugement, animé par une équipe formée à l'accueil des jeunes.</p>",
                'objectifs' => "<ul><li>Offrir un accueil confidentiel, gratuit et sans jugement aux 15-24 ans</li><li>Faciliter l'accès à l'information et aux services de santé sexuelle et reproductive</li><li>Créer des espaces de dialogue et d'activités entre jeunes</li><li>Orienter vers les structures de santé partenaires en cas de besoin</li></ul>",
                'activites' => "<ul><li>Accueil individuel, écoute et information personnalisée</li><li>Séances de sensibilisation et d'échange en groupe</li><li>Orientation vers le dépistage et le planning familial</li><li>Activités récréatives, culturelles et sportives</li></ul>",
                'resultats' => "<p>Un réseau de centres actif dans plusieurs villes du Bénin, fréquenté chaque année par des milliers de jeunes venus s'informer, échanger ou être orientés vers des services de santé adaptés.</p>",
            ],
            'MAG' => [
                'nom' => 'Magazine Amour&Vie',
                'couleur' => '#8b5cf6',
                'court' => "Le magazine historique du programme : des articles, des repères et des rubriques pour les jeunes, depuis 1996.",
                'description' => "<p>Composante historique du programme depuis sa création en 1996, le magazine Amour&Vie aborde sans détour les questions de santé sexuelle et reproductive qui préoccupent les jeunes béninois·es : puberté, contraception, IST/VIH, relations, consentement.</p><p>Rédigé dans un langage accessible et illustré, il est diffusé dans les établissements scolaires, les Centres Jeunes et lors des activités de sensibilisation.</p>",
                'objectifs' => "<ul><li>Diffuser une information fiable et accessible sur la santé sexuelle et reproductive</li><li>Donner la parole aux jeunes à travers des rubriques et des questions de lecteurs</li><li>Accompagner les grandes campagnes de sensibilisation du programme</li></ul>",
                'activites' => "<ul><li>Rédaction et publication de numéros réguliers</li><li>Collecte de questions et de retours de lecteurs</li><li>Distribution dans les écoles et les Centres Jeunes</li></ul>",
                'resultats' => "<p>Des dizaines de numéros publiés au fil des années, lus et partagés par des générations de jeunes béninois·es.</p>",
            ],
            'EMISSIONS' => [
                'nom' => 'Émissions Radio & TV',
                'couleur' => '#e3001b',
                'court' => "Des émissions radio et télévisées qui libèrent la parole des jeunes sur la sexualité, sans tabou.",
                'description' => "<p>Le volet radio et télévisé du programme Amour&Vie propose des émissions animées avec et pour les jeunes, diffusées sur des radios et chaînes partenaires à travers le Bénin.</p><p>Micro-trottoirs, débats et interventions de pairs-éducateur·rices y abordent, dans un langage direct, les questions de sexualité, de relations et de santé reproductive.</p>",
                'objectifs' => "<ul><li>Créer des plateformes de dialogue accessibles au plus grand nombre</li><li>Vulgariser l'information sur la santé sexuelle et reproductive</li><li>Donner la parole aux jeunes eux-mêmes, en tant qu'animateur·rices et invité·es</li></ul>",
                'activites' => "<ul><li>Production d'émissions radio régulières</li><li>Captations et diffusions télévisées ponctuelles</li><li>Micro-trottoirs recueillis sur le terrain</li></ul>",
                'resultats' => "<p>Des émissions suivies dans plusieurs régions du pays, contribuant à libérer la parole sur des sujets encore tabous.</p>",
            ],
            'PANTHERE' => [
                'nom' => 'Pair-éducation : Projet Panthère',
                'couleur' => '#6d3fd1',
                'court' => "Des jeunes formés pour informer et accompagner d'autres jeunes, entre pairs, avec confiance.",
                'description' => "<p>Le Projet Panthère forme des jeunes volontaires (les pairs-éducateur·rices) pour informer et accompagner leurs pairs sur les questions de santé sexuelle et reproductive, dans les écoles, les quartiers et les Centres Jeunes.</p><p>Cette approche de pair-éducation s'appuie sur la confiance et la proximité entre jeunes du même âge pour faciliter des échanges plus libres que face à un adulte.</p>",
                'objectifs' => "<ul><li>Former des pairs-éducateur·rices capables d'informer et d'orienter leurs pairs</li><li>Faciliter une prise de décision sexuelle responsable chez les jeunes</li><li>Améliorer la perception du risque lié aux IST et au VIH</li><li>Créer un réseau de jeunes relais dans les communautés</li></ul>",
                'activites' => "<ul><li>Formation initiale et continue des pairs-éducateur·rices</li><li>Séances de sensibilisation entre pairs dans les écoles et quartiers</li><li>Distribution de supports d'information et orientation vers les Centres Jeunes</li></ul>",
                'resultats' => "<p>Un réseau de pairs-éducateur·rices actif sur l'ensemble des zones d'intervention, régulièrement renouvelé et formé.</p>",
            ],
            'CAMPAGNES' => [
                'nom' => 'Campagnes de sensibilisation',
                'couleur' => '#aa0014',
                'court' => "Des campagnes ponctuelles pour faire bouger les lignes sur la santé sexuelle et reproductive des jeunes.",
                'description' => "<p>Amour&Vie mène régulièrement des campagnes de sensibilisation autour de dates clés (Journée mondiale de lutte contre le sida, Journée mondiale de la contraception…) ou de thématiques prioritaires identifiées avec les jeunes.</p><p>Ces campagnes mobilisent l'ensemble des composantes du programme (centres, magazine, émissions, pairs-éducateur·rices) autour des objectifs stratégiques du programme.</p>",
                'objectifs' => "<ul><li>Établir des plateformes de dialogue pour les jeunes</li><li>Faciliter une prise de décision sexuelle responsable</li><li>Améliorer la perception du risque lié aux IST et au VIH</li><li>Encourager le dialogue entre parents et enfants sur la sexualité</li></ul>",
                'activites' => "<ul><li>Campagnes de sensibilisation grand public</li><li>Événements et journées portes ouvertes autour de dates symboliques</li><li>Production de supports de communication dédiés</li></ul>",
                'resultats' => "<p>Une visibilité accrue des enjeux de santé sexuelle et reproductive des jeunes, portée par des campagnes relayées dans plusieurs villes du pays.</p>",
            ],
        ];

        $programmes = [];
        $ordre = 0;

        foreach ($definitions as $sigle => $donnees) {
            $programme = new Programme();
            $programme
                ->setSigle($sigle)
                ->setNom($donnees['nom'])
                ->setCouleur($donnees['couleur'])
                ->setDescriptionCourte($donnees['court'])
                ->setDescription($donnees['description'])
                ->setObjectifs($donnees['objectifs'])
                ->setActivites($donnees['activites'])
                ->setResultats($donnees['resultats'])
                ->setOrdre($ordre++)
                ->setActif(true);

            $manager->persist($programme);
            $programmes[$sigle] = $programme;
        }

        return $programmes;
    }

    /** @return array<string, Thematique> */
    private function chargerThematiques(ObjectManager $manager): array
    {
        $definitions = [
            'Santé sexuelle et reproductive' => '#1e9e6d',
            'VIH/IST' => '#e3001b',
            'Contraception et planification familiale' => '#8b5cf6',
            'Genre et égalité' => '#aa0014',
            'Consentement' => '#6d3fd1',
            'Dialogue parents-enfants' => '#167a54',
            'Pair-éducation' => '#7a6a64',
            'Puberté et développement' => '#5a4d48',
        ];

        $thematiques = [];
        $ordre = 0;

        foreach ($definitions as $nom => $couleur) {
            $thematique = new Thematique();
            $thematique->setNom($nom)->setCouleur($couleur)->setOrdre($ordre++);
            $manager->persist($thematique);
            $thematiques[$nom] = $thematique;
        }

        return $thematiques;
    }

    /** @return array<string, TypeArticle> */
    private function chargerTypesArticle(ObjectManager $manager): array
    {
        // NB : pas de type "Témoignage" ici — les témoignages ont désormais leur propre
        // entité dédiée (Temoignage, voir chargerTemoignages()) plutôt qu'un simple type d'article.
        $definitions = [
            'Actualité' => '#1e9e6d',
            'Événement' => '#8b5cf6',
            'Communiqué' => '#e3001b',
            'Analyse' => '#6d3fd1',
            'Story' => '#aa0014',
            'Interview' => '#7a6a64',
        ];

        $types = [];
        $ordre = 0;

        foreach ($definitions as $nom => $couleur) {
            $type = new TypeArticle();
            $type->setNom($nom)->setCouleur($couleur)->setOrdre($ordre++);
            $manager->persist($type);
            $types[$nom] = $type;
        }

        return $types;
    }

    /** @return array<string, TypePublication> */
    private function chargerTypesPublication(ObjectManager $manager): array
    {
        // Les numéros du magazine sont représentés via le type "Brochure" : pas besoin
        // d'un type "Magazine" dédié pour ce jeu de données de démonstration.
        $definitions = [
            'Rapport' => 'file-lines',
            'Étude' => 'chart-simple',
            'Guide' => 'book',
            'Note de politique' => 'file-lines',
            'Brochure' => 'book-open',
            'Newsletter' => 'newspaper',
            'Communiqué' => 'bullhorn',
            'Document institutionnel' => 'folder',
        ];

        $types = [];
        $ordre = 0;

        foreach ($definitions as $nom => $icone) {
            $type = new TypePublication();
            $type->setNom($nom)->setIcone($icone)->setOrdre($ordre++);
            $manager->persist($type);
            $types[$nom] = $type;
        }

        return $types;
    }

    private function chargerValeurs(ObjectManager $manager): void
    {
        $definitions = [
            ['Confidentialité', 'bouclier', '#1e9e6d', "Ce qui se dit dans un Centre Jeunes ou lors d'une séance de pair-éducation y reste : la confiance est la base de notre accompagnement."],
            ['Non-jugement', 'coeur', '#e3001b', "Chaque jeune est accueilli·e tel·le qu'il ou elle est, sans être jugé·e sur ses questions, ses choix ou son parcours."],
            ['Écoute', 'message', '#8b5cf6', "Nous prenons le temps d'entendre les besoins réels des jeunes avant d'y répondre, plutôt que de leur imposer des réponses toutes faites."],
            ['Inclusion', 'utilisateurs', '#6d3fd1', "Jeunes scolarisé·es, déscolarisé·es ou jamais scolarisé·es, filles et garçons : chacun·e a sa place dans le programme Amour&Vie."],
            ['Engagement des jeunes', 'poignee-de-main', '#aa0014', "Un programme des jeunes, pour les jeunes : ce sont les jeunes eux-mêmes qui portent une grande partie de nos actions, comme pairs-éducateur·rices ou animateur·rices."],
            ['Innovation', 'ampoule', '#167a54', "Nous adaptons nos formats (magazine, radio, TV, numérique) pour aller à la rencontre des jeunes là où ils et elles se trouvent."],
        ];

        $ordre = 0;
        foreach ($definitions as [$titre, $icone, $couleur, $description]) {
            $valeur = new Valeur();
            $valeur->setTitre($titre)->setIcone($icone)->setCouleur($couleur)
                ->setDescription($description)->setOrdre($ordre++)->setActif(true);
            $manager->persist($valeur);
        }
    }

    private function chargerChiffresCles(ObjectManager $manager): void
    {
        $definitions = [
            [30, '+', "Années d'engagement", 'calendar'],
            [13, '', 'Centres Jeunes Amour&Vie', 'diagram-project'],
            [50000, '+', 'Jeunes touchés chaque année', 'users'],
            [300, '+', "Pairs-éducateur·rices mobilisé·es", 'handshake'],
        ];

        $ordre = 0;
        foreach ($definitions as [$valeur, $suffixe, $libelle, $icone]) {
            $chiffre = new ChiffreCle();
            $chiffre->setValeur($valeur)->setSuffixe($suffixe)->setLibelle($libelle)
                ->setIcone($icone)->setOrdre($ordre++)->setActif(true);
            $manager->persist($chiffre);
        }
    }

    private function chargerSectionsDePage(ObjectManager $manager): void
    {
        $definitions = [
            [
                SectionPage::PAGE_ACCUEIL, 'presentation', 'Un programme des jeunes, pour les jeunes', 'Notre programme',
                "<p><strong>Amour&Vie</strong> est un programme de santé sexuelle et reproductive destiné aux jeunes béninois·es de 15 à 24 ans, initié en 1996 par PSI Bénin / ABMS (Association Béninoise pour le Marketing Social).</p>"
                ."<p>Nous informons, écoutons et accompagnons les jeunes (scolarisé·es, déscolarisé·es ou jamais scolarisé·es) à travers nos Centres Jeunes, notre magazine, nos émissions radio et TV, et notre réseau de pairs-éducateur·rices du Projet Panthère.</p>",
            ],
            [
                SectionPage::PAGE_QUI_SOMMES_NOUS, 'histoire', 'Notre histoire', 'Depuis 1996, aux côtés des jeunes',
                "<p>Le programme Amour&Vie a été lancé en 1996 par PSI Bénin / ABMS, avec la conviction que les jeunes sont les mieux placé·es pour parler aux jeunes de sexualité, de santé et de relations.</p>"
                ."<p>Trois composantes historiques ont structuré le programme dès l'origine : un magazine imprimé, une émission radio (aujourd'hui prolongée à la télévision), et le Projet Panthère, dispositif de pair-éducation porté par des jeunes volontaires formé·es.</p>"
                ."<p>Au fil des années, ce socle s'est élargi avec la création d'un réseau de Centres Jeunes Amour&Vie (CJAV) implantés dans plusieurs villes du Bénin, offrant un accueil physique, confidentiel et gratuit aux 15-24 ans.</p>",
            ],
            [
                SectionPage::PAGE_QUI_SOMMES_NOUS, 'mission', 'Notre mission', null,
                "<p>Accompagner les jeunes béninois·es dans leur santé et leurs droits sexuels et reproductifs, autour de quatre objectifs stratégiques : établir des plateformes de dialogue pour les jeunes, faciliter une prise de décision sexuelle responsable, améliorer la perception du risque lié aux IST et au VIH, et encourager le dialogue entre parents et enfants sur la sexualité.</p>",
            ],
            [
                SectionPage::PAGE_QUI_SOMMES_NOUS, 'vision', 'Notre vision', null,
                "<p>Une jeunesse béninoise informée, en bonne santé et capable de faire des choix libres et responsables concernant sa vie affective et sexuelle, dans un environnement familial et communautaire plus ouvert au dialogue.</p>",
            ],
            [
                SectionPage::PAGE_ESPACE_JEUNES, 'intro', 'Un espace rien que pour toi', null,
                "<p>Un espace confidentiel, sans jugement, pour toi. Ici, tu peux poser tes questions librement, découvrir le réseau des Centres Jeunes près de chez toi, et trouver des réponses fiables sur la santé sexuelle et reproductive, à ton rythme.</p>",
            ],
            [
                SectionPage::PAGE_RESSOURCES, 'intro', 'Des réponses fiables, sans tabou', null,
                "<p>Des réponses fiables à tes questions, sans tabou. Retrouve ici la FAQ construite à partir des questions posées par d'autres jeunes, le magazine, les émissions et les guides du programme Amour&Vie.</p>",
            ],
            [
                SectionPage::PAGE_TEMOIGNAGES, 'intro', 'Ils et elles témoignent', null,
                "<p>Des jeunes accompagné·es par le programme Amour&Vie partagent, en toute confidentialité, ce que le passage par un Centre Jeunes, une séance de pair-éducation ou une lecture du magazine a changé pour eux et elles.</p>",
            ],
        ];

        $ordre = 0;
        foreach ($definitions as [$page, $cle, $titre, $sousTitre, $contenu]) {
            $section = new SectionPage();
            $section->setPage($page)->setCle($cle)->setTitre($titre)
                ->setSousTitre($sousTitre)->setContenu($contenu)
                ->setOrdre($ordre++)->setActif(true);
            $manager->persist($section);
        }
    }

    private function chargerEquipe(ObjectManager $manager): void
    {
        // Noms fictifs, sans lien avec l'équipe réelle d'Amour&Vie ABMS : à remplacer avant
        // mise en production.
        $definitions = [
            // [prénom, nom, fonction, département, catégorie]
            ['Émilienne', 'Houngbédji', "Présidente du Conseil d'administration", null, CategorieMembre::CONSEIL_ADMINISTRATION],
            ['Gérard', 'Amoussou', 'Vice-président', null, CategorieMembre::CONSEIL_ADMINISTRATION],
            ['Rachidatou', 'Bio Tchané', 'Trésorière', null, CategorieMembre::CONSEIL_ADMINISTRATION],
            ['Sylvain', 'Kpadonou', 'Secrétaire général', null, CategorieMembre::CONSEIL_ADMINISTRATION],

            ['Kossi', 'Adjovi', "Coordonnateur·rice national·e du programme", 'Direction', CategorieMembre::COORDINATION],
            ['Béatrice', 'Zinsou', 'Coordonnatrice adjointe', 'Direction', CategorieMembre::COORDINATION],
            ['Marius', 'Dossou', 'Responsable des Centres Jeunes', 'Centres Jeunes', CategorieMembre::COORDINATION],

            ['Bernadette', 'Sossou', 'Chargée de communication', 'Communication', CategorieMembre::EQUIPE_TECHNIQUE],
            ['Ibrahim', 'Yacoubou', 'Chargé du Magazine', 'Magazine', CategorieMembre::EQUIPE_TECHNIQUE],
            ['Fatima', 'Aholou', "Pair-éducatrice référente", 'Projet Panthère', CategorieMembre::EQUIPE_TECHNIQUE],
            ['Pascal', 'Agbodjan', 'Chargé du suivi-évaluation', 'Suivi-évaluation', CategorieMembre::EQUIPE_TECHNIQUE],
            ['Nadège', 'Todjinou', 'Responsable administrative et financière', 'Administration et finances', CategorieMembre::EQUIPE_TECHNIQUE],
            ['Roland', 'Chabi', 'Animateur radio', 'Émissions Radio & TV', CategorieMembre::EQUIPE_TECHNIQUE],
        ];

        $ordre = 0;
        foreach ($definitions as [$prenom, $nom, $fonction, $departement, $categorie]) {
            $membre = new Membre();
            $membre->setPrenom($prenom)->setNom($nom)->setFonction($fonction)
                ->setDepartement($departement)->setCategorie($categorie)
                ->setOrdre($ordre++)->setActif(true);
            $manager->persist($membre);
        }
    }

    private function chargerPartenaires(ObjectManager $manager): void
    {
        $definitions = [
            ['PSI / ABMS (Population Services International)', 'Réseau international', "Organisation à l'origine du programme Amour&Vie, initié en 1996."],
            ['Ministère de la Santé', 'Institution nationale', null],
            ['UNFPA Bénin', 'Partenaire technique et financier', null],
            ['USAID', 'Partenaire financier', null],
            ["Ministère de la Jeunesse, des Sports et de la Culture", 'Institution nationale', null],
            ['Scoutisme Béninois', 'Société civile', "Co-organisateur du Camp des jeunes volontaires Amour&Vie."],
            ['Réseau des Centres Jeunes partenaires', 'Société civile', null],
            ['Association béninoise pour la santé des jeunes', 'ONG locale', null],
        ];

        $ordre = 0;
        foreach ($definitions as [$nom, $categorie, $description]) {
            $partenaire = new Partenaire();
            $partenaire->setNom($nom)->setCategorie($categorie)->setDescription($description)
                ->setOrdre($ordre++)->setActif(true);
            $manager->persist($partenaire);
        }
    }

    /**
     * @param array<string, Programme>      $programmes
     * @param array<string, Thematique>     $thematiques
     * @param array<string, TypeArticle>    $types
     * @param array<string, Utilisateur>    $utilisateurs
     */
    private function chargerArticles(
        ObjectManager $manager,
        array $programmes,
        array $thematiques,
        array $types,
        array $utilisateurs,
    ): void {
        $definitions = [
            [
                'Un nouveau Centre Jeunes Amour&Vie ouvre ses portes à Dangbo',
                "Le nouveau centre proposera écoute, information et orientation gratuites et confidentielles aux jeunes de 15 à 24 ans de la commune.",
                'CJAV', 'Actualité', ['Santé sexuelle et reproductive', 'Contraception et planification familiale'], 'Dangbo', 3, true,
            ],
            [
                'Le magazine Amour&Vie consacre son nouveau numéro au consentement',
                "Ce numéro propose des repères simples pour comprendre ce qu'est le consentement et comment le faire respecter au quotidien.",
                'MAG', 'Actualité', ['Consentement'], 'Cotonou', 9, true,
            ],
            [
                '« Parlons contraception » : le nouvel épisode de l\'émission radio Amour&Vie',
                "Un épisode consacré aux idées reçues sur la contraception, avec les questions envoyées par les auditeurs et auditrices.",
                'EMISSIONS', 'Actualité', ['Contraception et planification familiale'], 'Porto-Novo', 16, false,
            ],
            [
                '« On m\'écoute sans me juger » : le regard d\'une jeune du Centre Jeunes de Bohicon',
                "Une jeune fréquentant régulièrement le centre raconte ce que l'accueil confidentiel a changé dans son quotidien.",
                'CJAV', 'Story', ['Santé sexuelle et reproductive'], 'Bohicon', 24, true,
            ],
            [
                'Communiqué : lancement de la campagne de dépistage VIH/IST',
                "Amour&Vie ABMS et ses partenaires lancent une campagne de dépistage gratuit à destination des jeunes dans plusieurs villes du pays.",
                'CAMPAGNES', 'Communiqué', ['VIH/IST'], 'Cotonou', 31, false,
            ],
            [
                'Formation de nouveaux pairs-éducateur·rices du Projet Panthère à Savalou',
                "Une trentaine de jeunes volontaires ont suivi une session intensive avant de rejoindre le réseau de pair-éducation de leur commune.",
                'PANTHERE', 'Actualité', ['Pair-éducation'], 'Savalou', 45, false,
            ],
            [
                'Journée portes ouvertes au Centre Jeunes d\'Abomey-Calavi',
                "Visites guidées, activités et échanges avec l'équipe du centre pour permettre aux familles de découvrir les services proposés.",
                'CJAV', 'Événement', ['Santé sexuelle et reproductive', 'Dialogue parents-enfants'], 'Abomey-Calavi', 58, false,
            ],
            [
                'Comprendre le consentement : ce que ça change au quotidien',
                "Une analyse accessible pour aider les jeunes et les adultes qui les entourent à mieux cerner ce que recouvre le consentement.",
                'CAMPAGNES', 'Analyse', ['Consentement', 'Genre et égalité'], 'Cotonou', 72, false,
            ],
            [
                '« Le pair-éducateur m\'a aidée à trouver les mots pour en parler à ma mère »',
                "Une jeune de Parakou revient sur le rôle qu'a joué un pair-éducateur dans l'ouverture du dialogue avec sa famille.",
                'PANTHERE', 'Interview', ['Dialogue parents-enfants', 'Pair-éducation'], 'Parakou', 86, false,
            ],
            [
                'Le Projet Panthère élargit son réseau de pairs-éducateur·rices dans le Borgou',
                "De nouveaux relais communautaires ont été formés pour renforcer la présence du programme dans le nord du pays.",
                'PANTHERE', 'Actualité', ['Pair-éducation'], 'Parakou', 100, false,
            ],
            [
                'Journée mondiale de lutte contre le sida : mobilisation dans six villes',
                "Stands de dépistage, animations et diffusion de supports d'information ont rassemblé plusieurs milliers de jeunes à travers le pays.",
                null, 'Événement', ['VIH/IST'], 'Cotonou', 115, false,
            ],
            [
                'Bilan de l\'année : ce qu\'Amour&Vie a changé pour les jeunes du Bénin',
                "Le programme dresse le bilan de ses activités auprès des jeunes : centres, magazine, émissions et pair-éducation.",
                null, 'Actualité', ['Santé sexuelle et reproductive'], 'Cotonou', 130, false,
            ],
        ];

        foreach ($definitions as $index => [$titre, $resume, $sigleProgramme, $typeNom, $nomsThematiques, $lieu, $joursAvant, $aLaUne]) {
            $article = new Article();
            $article
                ->setTitre($titre)
                ->setResume($resume)
                ->setContenu($this->contenuArticle($titre, $resume))
                ->setType($types[$typeNom])
                ->setLieu($lieu)
                ->setStatut(StatutArticle::PUBLIE)
                ->setDatePublication(new \DateTimeImmutable(sprintf('-%d days', $joursAvant)))
                ->setALaUne($aLaUne)
                ->setNombreVues(random_int(35, 900))
                ->setAuteur($utilisateurs['editeur']);

            if (null !== $sigleProgramme) {
                $article->setProgramme($programmes[$sigleProgramme]);
            }

            foreach ($nomsThematiques as $nomThematique) {
                $article->addThematique($thematiques[$nomThematique]);
            }

            $manager->persist($article);
        }

        // Deux contenus non publiés, pour illustrer le workflow éditorial (§ 3.2).
        $brouillon = new Article();
        $brouillon
            ->setTitre('Note de cadrage : évaluation à mi-parcours du Projet Panthère')
            ->setResume("Document de travail préparatoire à l'évaluation externe du dispositif de pair-éducation prévue au prochain semestre.")
            ->setContenu('<p>Contenu en cours de rédaction.</p>')
            ->setType($types['Analyse'])
            ->setProgramme($programmes['PANTHERE'])
            ->setStatut(StatutArticle::BROUILLON)
            ->setDatePublication(new \DateTimeImmutable())
            ->setAuteur($utilisateurs['contributeur']);
        $manager->persist($brouillon);

        $enAttente = new Article();
        $enAttente
            ->setTitre('Retour sur la journée portes ouvertes du Centre Jeunes de Kandi')
            ->setResume("Compte rendu de la journée organisée avec les autorités locales et les familles de la commune.")
            ->setContenu('<p>Compte rendu soumis à la relecture de la coordination.</p>')
            ->setType($types['Actualité'])
            ->setProgramme($programmes['CJAV'])
            ->setStatut(StatutArticle::EN_ATTENTE)
            ->setDatePublication(new \DateTimeImmutable())
            ->setAuteur($utilisateurs['contributeur']);
        $manager->persist($enAttente);
    }

    /**
     * @param array<string, Programme>       $programmes
     * @param array<string, Thematique>      $thematiques
     * @param array<string, TypePublication> $types
     */
    private function chargerPublications(
        ObjectManager $manager,
        array $programmes,
        array $thematiques,
        array $types,
    ): void {
        $anneeCourante = (int) date('Y');

        $definitions = [
            ['Rapport annuel d\'activités', 'Rapport', null, $anneeCourante - 1, ['Santé sexuelle et reproductive'], "Bilan complet des activités menées auprès des jeunes et de l'utilisation des ressources."],
            ['Amour&Vie Magazine : numéro spécial contraception', 'Brochure', 'MAG', $anneeCourante, ['Contraception et planification familiale'], "Un numéro entièrement consacré aux méthodes de contraception, sous forme de questions-réponses."],
            ['Guide du pair-éducateur Projet Panthère', 'Guide', 'PANTHERE', $anneeCourante - 1, ['Pair-éducation'], "Manuel pratique à destination des pairs-éducateur·rices formé·es : postures, techniques et outils d'animation."],
            ['Étude sur les connaissances des jeunes en santé sexuelle et reproductive', 'Étude', 'CAMPAGNES', $anneeCourante - 2, ['Santé sexuelle et reproductive', 'VIH/IST'], "Analyse des connaissances, attitudes et pratiques des jeunes béninois·es en matière de santé sexuelle."],
            ['Note de politique : renforcer le dialogue parents-enfants sur la sexualité', 'Note de politique', 'CAMPAGNES', $anneeCourante - 1, ['Dialogue parents-enfants'], "Recommandations adressées aux décideurs publics pour favoriser le dialogue intergénérationnel."],
            ['Manuel pédagogique d\'éducation à la vie sexuelle et affective', 'Guide', 'PANTHERE', $anneeCourante - 2, ['Puberté et développement', 'Consentement'], "Séquences pédagogiques clés en main pour les animations en milieu scolaire et communautaire."],
            ['VIH/IST au Bénin chez les jeunes : état des lieux', 'Étude', 'CAMPAGNES', $anneeCourante - 1, ['VIH/IST'], "Diagnostic de l'exposition et de l'accès au dépistage des jeunes de 15 à 24 ans."],
            ['Rapport d\'activités des Centres Jeunes Amour&Vie', 'Rapport', 'CJAV', $anneeCourante - 1, ['Santé sexuelle et reproductive'], "Bilan de fréquentation et de services rendus par le réseau des Centres Jeunes."],
            ['Brochure de présentation d\'Amour&Vie ABMS', 'Brochure', null, $anneeCourante, ['Santé sexuelle et reproductive'], "Document de présentation générale du programme, de sa mission et de ses composantes."],
            ['Lettre d\'information : premier semestre', 'Newsletter', null, $anneeCourante, ['Santé sexuelle et reproductive'], "Actualités du programme, temps forts et perspectives."],
            ['Statuts et règlement intérieur', 'Document institutionnel', null, $anneeCourante - 3, [], "Textes fondateurs de l'association porteuse du programme."],
        ];

        foreach ($definitions as [$titre, $typeNom, $sigleProgramme, $annee, $nomsThematiques, $description]) {
            $publication = new Publication();
            $publication
                ->setTitre($titre)
                ->setDescription('<p>'.$description.'</p>')
                ->setType($types[$typeNom])
                ->setAnnee($annee)
                ->setPublie(true)
                ->setNombreTelechargements(random_int(10, 480));

            if (null !== $sigleProgramme) {
                $publication->setProgramme($programmes[$sigleProgramme]);
            }

            foreach ($nomsThematiques as $nomThematique) {
                $publication->addThematique($thematiques[$nomThematique]);
            }

            $manager->persist($publication);
        }
    }

    /** @param array<string, Programme> $programmes */
    private function chargerEvenements(ObjectManager $manager, array $programmes): void
    {
        $definitions = [
            ['Formation de pairs-éducateur·rices Projet Panthère', 'Parakou', 'PANTHERE', 14],
            ['Journée portes ouvertes du Centre Jeunes de Cotonou', 'Cotonou', 'CJAV', 28],
            ['Enregistrement public de l\'émission radio Amour&Vie', 'Cotonou', 'EMISSIONS', 45],
            ['Journée mondiale de la contraception', 'Porto-Novo', 'CAMPAGNES', 62],
            ['Lancement du nouveau numéro du magazine Amour&Vie', 'Cotonou', 'MAG', 21],
            ['Assemblée générale annuelle Amour&Vie ABMS', 'Cotonou', null, 90],
            ['Camp des jeunes volontaires Amour&Vie', 'Bohicon', null, -20],
            ['Journée mondiale de lutte contre le sida', 'Cotonou', 'CAMPAGNES', -45],
        ];

        foreach ($definitions as [$titre, $lieu, $sigleProgramme, $joursApres]) {
            $evenement = new Evenement();
            $evenement
                ->setTitre($titre)
                ->setLieu($lieu)
                ->setDateDebut(new \DateTimeImmutable(sprintf('%+d days 09:00', $joursApres)))
                ->setDateFin(new \DateTimeImmutable(sprintf('%+d days 17:00', $joursApres + 1)))
                ->setDescription(
                    '<p>'.$titre.' organisé par Amour&Vie ABMS à '.$lieu.'.</p>'
                    .'<p>Cette rencontre réunira les jeunes et les équipes du programme autour des activités du jour, '
                    .'avec des temps d\'échange, d\'information et d\'animation.</p>'
                )
                ->setPublie(true);

            if (null !== $sigleProgramme) {
                $evenement->setProgramme($programmes[$sigleProgramme]);
            }

            $manager->persist($evenement);
        }
    }

    /**
     * @param array<string, Programme>  $programmes
     * @param array<string, Thematique> $thematiques
     *
     * @return array<string, CentreJeune>
     */
    private function chargerCentresJeunes(ObjectManager $manager, array $programmes, array $thematiques): array
    {
        // Adresses précises, téléphones et responsables fictifs : à valider avec l'équipe
        // Amour&Vie ABMS avant la mise en production. Seules les coordonnées approximatives
        // des centres-villes (information géographique publique) sont utilisées ci-dessous.
        $definitions = [
            ['Cotonou', 'Quartier Ganhi, non loin du marché Dantokpa', '6.3703', '2.3912', ['Santé sexuelle et reproductive', 'VIH/IST']],
            ['Abomey-Calavi', 'Quartier Godomey, axe principal', '6.4489', '2.3553', ['Contraception et planification familiale', 'Puberté et développement']],
            ['Porto-Novo', 'Quartier Djassin, proche du grand marché', '6.4969', '2.6289', ['Santé sexuelle et reproductive', 'Dialogue parents-enfants']],
            ['Dangbo', 'Centre-ville, proche du marché communal', '6.5833', '2.5667', ['VIH/IST']],
            ['Bohicon', 'Quartier Agbokou', '7.1782', '2.0667', ['Contraception et planification familiale']],
            ['Savalou', 'Quartier Kpataba', '7.9285', '1.9762', ['Pair-éducation']],
            ['Kandi', 'Quartier administratif', '11.1342', '2.9386', ['Santé sexuelle et reproductive', 'VIH/IST']],
            ['Tchaourou', 'Centre-ville', '8.8862', '2.5964', ['Consentement']],
            ['Bassila', 'Quartier central', '9.0167', '1.6667', ['Puberté et développement', 'Genre et égalité']],
        ];

        $centres = [];
        $ordre = 0;

        foreach ($definitions as [$ville, $rue, $latitude, $longitude, $nomsThematiques]) {
            $centre = new CentreJeune();
            $centre
                ->setNom('Centre Jeunes Amour&Vie '.$ville)
                ->setVille($ville)
                ->setAdresse($rue.", ".$ville.", République du Bénin")
                ->setDescription(
                    "<p>Le Centre Jeunes Amour&Vie de {$ville} accueille gratuitement et confidentiellement les jeunes de 15 à 24 "
                    ."ans pour de l'écoute, de l'information et des services de santé sexuelle et reproductive.</p>"
                )
                ->setTelephone('+229 21 00 00 '.str_pad((string) ($ordre + 10), 2, '0', STR_PAD_LEFT))
                ->setEmail('centre.'.strtolower(str_replace([' ', '-', "'"], ['', '', ''], $ville)).'@amouretvie.bj')
                ->setHoraires('Du lundi au samedi, de 8h00 à 18h00')
                ->setResponsable("Responsable du centre")
                ->setLatitude($latitude)
                ->setLongitude($longitude)
                ->setProgramme($programmes['CJAV'])
                ->setOrdre($ordre++)
                ->setActif(true);

            foreach ($nomsThematiques as $nomThematique) {
                $centre->addThematique($thematiques[$nomThematique]);
            }

            $manager->persist($centre);
            $centres[$ville] = $centre;
        }

        return $centres;
    }

    /**
     * @param array<string, Programme>  $programmes
     * @param array<string, Thematique> $thematiques
     */
    private function chargerEmissions(ObjectManager $manager, array $programmes, array $thematiques): void
    {
        $definitions = [
            ['Parlons contraception', TypeEmission::RADIO, 5, 'Rafiatou G. et Steve A.', ['Contraception et planification familiale']],
            ['Ados et consentement : le débat', TypeEmission::RADIO, 12, 'Steve A.', ['Consentement']],
            ['Le micro-trottoir des Centres Jeunes', TypeEmission::RADIO, 19, 'Équipe Amour&Vie', ['Santé sexuelle et reproductive']],
            ['VIH/IST : dépistage et prévention', TypeEmission::RADIO, 26, 'Rafiatou G.', ['VIH/IST']],
            ['Parents-enfants : oser le dialogue', TypeEmission::TV, 33, 'Corneille Z.', ['Dialogue parents-enfants']],
            ["Les pairs-éducateur·rices prennent le micro", TypeEmission::RADIO, 40, 'Réseau Projet Panthère', ['Pair-éducation']],
            ["Puberté : les questions qu'on n'ose pas poser", TypeEmission::RADIO, 47, 'Steve A. et Rafiatou G.', ['Puberté et développement']],
            ['Zoom sur le Projet Panthère', TypeEmission::TV, 54, 'Corneille Z.', ['Pair-éducation']],
            ['Amour&Vie Magazine, le debrief', TypeEmission::RADIO, 61, 'Rafiatou G.', ['Santé sexuelle et reproductive']],
            ['Grossesses précoces : en parler sans tabou', TypeEmission::RADIO, 68, 'Steve A.', ['Contraception et planification familiale', 'Dialogue parents-enfants']],
            ['Genre et égalité chez les jeunes', TypeEmission::TV, 75, 'Corneille Z.', ['Genre et égalité']],
            ['Spécial Journée mondiale du sida', TypeEmission::TV, 90, 'Corneille Z. et Rafiatou G.', ['VIH/IST']],
        ];

        $ordre = 0;
        foreach ($definitions as [$titre, $type, $joursAvant, $animateurs, $nomsThematiques]) {
            $emission = new Emission();
            $emission
                ->setTitre($titre)
                ->setType($type)
                ->setDescription("<p>Un épisode {$type->libelle()} du programme Amour&Vie, animé avec et pour les jeunes.</p>")
                // La plupart des épisodes n'ont pas encore de lien externe : à compléter au fil
                // des diffusions réelles (YouTube, SoundCloud…) plutôt que d'inventer une URL.
                ->setUrlExterne(null)
                ->setDateDiffusion(new \DateTimeImmutable(sprintf('-%d days', $joursAvant)))
                ->setAnimateurs($animateurs)
                ->setProgramme($programmes['EMISSIONS'])
                ->setPublie(true)
                ->setOrdre($ordre++);

            foreach ($nomsThematiques as $nomThematique) {
                $emission->addThematique($thematiques[$nomThematique]);
            }

            $manager->persist($emission);
        }
    }

    private function chargerTemoignages(ObjectManager $manager): void
    {
        // Témoignages fictifs représentant des profils types de jeunes bénéficiaires,
        // à des fins de démonstration uniquement — aucune personne réelle n'est visée.
        $definitions = [
            ['Anonyme', 19, 'Cotonou', "J'avais peur d'aller au Centre Jeunes, je pensais qu'on allait me juger. Finalement, l'équipe m'a écoutée sans aucun jugement et m'a expliqué calmement mes options de contraception. Je suis repartie avec des réponses claires, ça change tout.", StatutModeration::PUBLIE],
            ['Sandrine', 21, 'Porto-Novo', "Grâce à une séance de pair-éducation, j'ai enfin trouvé les mots pour parler de sexualité avec ma mère. Ça n'a pas été facile au début, mais aujourd'hui on peut en discuter sans tension.", StatutModeration::PUBLIE],
            [null, 17, 'Bohicon', "Le magazine Amour&Vie m'a aidée à comprendre ce qui m'arrivait pendant la puberté, à un moment où je n'osais poser aucune question à personne autour de moi.", StatutModeration::PUBLIE],
            ['Judicaël', 23, 'Parakou', "Je suis allé me faire dépister au Centre Jeunes par curiosité, sans trop y croire. L'accueil a été rassurant du début à la fin, et j'ai reparlé du dépistage à plusieurs amis depuis.", StatutModeration::PUBLIE],
            ['Anonyme', 16, 'Dangbo', "Pendant une animation avec un pair-éducateur, j'ai enfin compris ce que voulait dire consentement. Depuis, je sais mieux poser mes limites et les respecter chez les autres.", StatutModeration::PUBLIE],
            ['Rafiatou', 20, 'Savalou', "J'écoute l'émission radio Amour&Vie depuis des mois. Entendre d'autres jeunes poser les mêmes questions que moi m'a fait sentir moins seule face à mes doutes.", StatutModeration::PUBLIE],
            ['Anonyme', 18, 'Kandi', "Le Centre Jeunes de ma ville vient d'ouvrir et j'y suis déjà passée deux fois. C'est rassurant d'avoir un endroit proche de chez moi où poser mes questions sans détour.", StatutModeration::EN_ATTENTE],
            ['Enagnon', 22, 'Cotonou', "Devenir pair-éducateur a changé ma manière de voir mon rôle auprès de mes amis : je me sens utile quand je peux les orienter vers de bonnes informations plutôt que des rumeurs.", StatutModeration::EN_ATTENTE],
        ];

        foreach ($definitions as [$pseudonyme, $age, $ville, $contenu, $statut]) {
            $temoignage = new Temoignage();
            $temoignage
                ->setPseudonyme($pseudonyme)
                ->setAge($age)
                ->setVille($ville)
                ->setContenu($contenu)
                ->setStatut($statut);

            $manager->persist($temoignage);
        }
    }

    /** @param array<string, Thematique> $thematiques */
    private function chargerQuestionsAnonymes(ObjectManager $manager, array $thematiques): void
    {
        $repondant = "Un·e pair-éducateur·rice Amour&Vie";

        // Questions publiées, avec leur réponse : alimentent la FAQ publique de l'espace Ressources.
        $publiees = [
            [
                'Est-ce qu\'on peut tomber enceinte dès la première fois ?',
                "Oui, c'est tout à fait possible : une grossesse dépend du cycle et de l'ovulation, pas du nombre de fois. Utiliser un moyen de contraception (préservatif, pilule…) dès le premier rapport protège contre une grossesse non désirée et contre les IST. Un·e pair-éducateur·rice ou l'équipe d'un Centre Jeunes peut t'aider à choisir la méthode qui te convient.",
                ['Contraception et planification familiale'],
            ],
            [
                'Comment se faire dépister du VIH gratuitement ?',
                "Le dépistage du VIH est gratuit dans les centres de santé publics et dans plusieurs Centres Jeunes Amour&Vie partenaires. Le test est rapide, confidentiel, et le résultat t'est communiqué en privé, avec un accompagnement si besoin. N'hésite pas à passer au Centre Jeunes le plus proche pour connaître les points de dépistage disponibles.",
                ['VIH/IST'],
            ],
            [
                'Comment en parler à mes parents sans que ça tourne mal ?',
                "Il n'y a pas de recette magique, mais choisir un moment calme, commencer par une question simple plutôt qu'une annonce, et accepter que la première réaction ne soit pas parfaite peuvent aider. Certain·es jeunes préfèrent d'abord s'entraîner à trouver les mots avec un·e pair-éducateur·rice. Le dialogue parents-enfants se construit dans le temps, ce n'est pas un échec si c'est encore difficile au début.",
                ['Dialogue parents-enfants'],
            ],
            [
                "C'est quoi exactement le consentement ?",
                "Le consentement, c'est un accord libre, clair et réversible à un moment donné : personne ne peut décider à ta place, et tu peux changer d'avis à tout moment, même en cours de route. Un silence ou une absence de refus n'est pas un consentement. Si quelque chose te met mal à l'aise, tu as le droit de dire non, et cela doit être respecté.",
                ['Consentement'],
            ],
            [
                'Est-ce normal d\'avoir des règles irrégulières à mon âge ?',
                "Oui, c'est fréquent dans les premières années suivant les premières règles : le cycle peut mettre du temps à se régulariser. Si les règles sont très douloureuses, très abondantes, ou totalement absentes pendant plusieurs mois, il est utile d'en parler à un·e professionnel·le de santé, notamment dans un Centre Jeunes.",
                ['Puberté et développement'],
            ],
            [
                'Le préservatif protège-t-il vraiment à 100% ?',
                "Bien utilisé, du début à la fin du rapport et à chaque fois, le préservatif est très efficace contre les IST/VIH et les grossesses non désirées, mais aucune méthode n'est efficace à 100%. Vérifier la date de péremption, l'ouvrir avec précaution et le poser correctement fait toute la différence. Dans un Centre Jeunes, l'équipe peut te montrer comment bien l'utiliser.",
                ['Contraception et planification familiale', 'VIH/IST'],
            ],
            [
                'Peut-on attraper une IST sans avoir de symptômes ?',
                "Oui, plusieurs IST peuvent être présentes sans aucun symptôme visible, ce qui ne les rend pas moins transmissibles. C'est pourquoi le dépistage régulier est important dès qu'on a des rapports sexuels, même sans signe apparent. Un dépistage n'est jamais un jugement, c'est un geste de santé.",
                ['VIH/IST'],
            ],
            [
                'Est-ce grave de ne pas être encore réglée à 15 ans ?',
                "L'âge des premières règles varie beaucoup d'une personne à l'autre, en général entre 9 et 16 ans. En dessous de 16 ans, il n'y a généralement pas d'inquiétude à avoir, mais si tu es proche de cet âge et que tu es inquiète, en parler à un·e professionnel·le de santé permet d'être rassurée.",
                ['Puberté et développement'],
            ],
            [
                "Comment savoir si une relation est saine ou pas ?",
                "Une relation saine repose sur le respect mutuel, l'écoute, et le fait de pouvoir dire non sans peur des conséquences. Te sentir contrôlé·e, rabaissé·e, ou être forcé·e à faire des choses contre ton gré sont des signaux d'alerte. Tu peux en parler à un·e pair-éducateur·rice ou à un adulte de confiance.",
                ['Genre et égalité', 'Consentement'],
            ],
            [
                "Où trouver de la contraception d'urgence en cas de besoin ?",
                "La contraception d'urgence (pilule du lendemain) est disponible en pharmacie et dans les centres de santé ; elle est plus efficace si elle est prise le plus tôt possible après le rapport, dans les 72 heures. Les Centres Jeunes Amour&Vie peuvent t'orienter vers le point le plus proche.",
                ['Contraception et planification familiale'],
            ],
        ];

        $ordre = 0;
        foreach ($publiees as [$question, $reponse, $nomsThematiques]) {
            $questionAnonyme = new QuestionAnonyme();
            $questionAnonyme
                ->setQuestion($question)
                ->setReponse($reponse)
                ->setRepondantLibre($repondant)
                ->setStatut(StatutQuestion::PUBLIEE_FAQ)
                ->setOrdre($ordre++)
                ->setRepondueAt(new \DateTimeImmutable(sprintf('-%d days', random_int(5, 120))));

            foreach ($nomsThematiques as $nomThematique) {
                $questionAnonyme->addThematique($thematiques[$nomThematique]);
            }

            $manager->persist($questionAnonyme);
        }

        // Questions en attente de réponse, pour illustrer la file de modération.
        $enAttente = [
            'Est-ce que la pilule contraceptive fait grossir ?',
            "Comment reconnaître les signes d'une IST chez un garçon ?",
            'Peut-on faire confiance à un test de grossesse acheté en pharmacie ?',
        ];

        foreach ($enAttente as $question) {
            $questionAnonyme = new QuestionAnonyme();
            $questionAnonyme
                ->setQuestion($question)
                ->setStatut(StatutQuestion::EN_ATTENTE)
                ->setOrdre($ordre++);

            $manager->persist($questionAnonyme);
        }
    }

    private function chargerNewsletterAbonnes(ObjectManager $manager): void
    {
        // Adresses clairement fictives, générées pour la démonstration.
        $definitions = [
            ['Aïcha', 'aicha.sounon@exemple.bj', true],
            ['Judicaël', 'judicael.houenou@exemple.bj', true],
            ['Rafiatou', 'rafiatou.gbaguidi@exemple.bj', true],
            ['Enagnon', 'enagnon.dossou@exemple.bj', true],
            ['Sandrine', 'sandrine.aina@exemple.bj', true],
            ['Corneille', 'corneille.zannou@exemple.bj', true],
            ['Fadilatou', 'fadilatou.alassane@exemple.bj', true],
            ['Wilfried', 'wilfried.kpobi@exemple.bj', true],
            ['Nourath', 'nourath.chabi@exemple.bj', true],
            ['Steve', 'steve.agossou@exemple.bj', true],
            ['Divine', 'divine.houngbo@exemple.bj', true],
            ['Moufoutaou', 'moufoutaou.baba@exemple.bj', true],
            ['Prisca', 'prisca.adjahoui@exemple.bj', true],
            ['Ezéchiel', 'ezechiel.sonon@exemple.bj', true],
            ['Latifatou', 'latifatou.yessoufou@exemple.bj', true],
            ['Grâce', 'grace.loko@exemple.bj', false],
            ['Ulrich', 'ulrich.hounkpe@exemple.bj', true],
            ['Bertille', 'bertille.kora@exemple.bj', false],
        ];

        foreach ($definitions as [$prenom, $email, $actif]) {
            $abonne = new NewsletterAbonne();
            $abonne->setPrenom($prenom)->setEmail($email)->setActif($actif);
            $manager->persist($abonne);
        }
    }

    /** @param array<string, Thematique> $thematiques */
    private function chargerQuiz(ObjectManager $manager, array $thematiques): void
    {
        $definitions = [
            [
                'Teste tes connaissances sur la contraception',
                "Vérifie ce que tu sais sur les méthodes de contraception disponibles pour les jeunes au Bénin.",
                ['Contraception et planification familiale'],
                [
                    [
                        'Le préservatif protège-t-il à la fois contre les grossesses et les IST ?',
                        "Bien utilisé, le préservatif est le seul moyen de contraception qui protège aussi contre les IST, dont le VIH.",
                        [['Oui', true], ['Non', false], ['Seulement contre les IST', false], ['Seulement contre les grossesses', false]],
                    ],
                    [
                        'La pilule contraceptive protège-t-elle contre les IST ?',
                        "La pilule empêche une grossesse mais ne protège pas contre les IST : il faut associer un préservatif pour se protéger des deux.",
                        [['Oui', false], ['Non', true], ['Seulement le VIH', false], ['Seulement certaines IST', false]],
                    ],
                    [
                        'Quel est le délai maximum pour prendre une contraception d\'urgence après un rapport non protégé ?',
                        "Plus elle est prise tôt, plus la contraception d'urgence est efficace ; elle doit être prise au plus tard dans les 72 heures.",
                        [['24 heures', false], ['72 heures', true], ['7 jours', false], ['1 mois', false]],
                    ],
                    [
                        'Peut-on tomber enceinte pendant les règles ?',
                        "C'est rare mais possible, notamment en cas de cycle court ou irrégulier : cette période ne doit pas être considérée comme totalement sûre.",
                        [['Jamais', false], ["Oui, c'est possible", true], ['Seulement le dernier jour', false], ['Seulement si le cycle est irrégulier', false]],
                    ],
                    [
                        'Où peut-on obtenir information et contraception au Bénin, gratuitement, à 15-24 ans ?',
                        "Les Centres Jeunes Amour&Vie et les centres de santé proposent information et méthodes de contraception, gratuitement et confidentiellement, aux jeunes de 15 à 24 ans.",
                        [['Dans un Centre Jeunes Amour&Vie ou un centre de santé', true], ['Seulement en pharmacie', false], ['Nulle part avant 18 ans', false], ["Seulement à l'hôpital", false]],
                    ],
                ],
            ],
            [
                'VIH/IST : vrai ou faux ?',
                "Un quiz pour démêler le vrai du faux sur le VIH et les infections sexuellement transmissibles.",
                ['VIH/IST'],
                [
                    [
                        'On peut savoir si une personne a une IST juste en la regardant.',
                        "De nombreuses IST ne présentent aucun symptôme visible : seul un dépistage permet de savoir.",
                        [['Vrai', false], ['Faux', true]],
                    ],
                    [
                        'Le dépistage du VIH est gratuit au Bénin.',
                        "Le dépistage du VIH est gratuit dans les centres de santé publics et plusieurs Centres Jeunes Amour&Vie.",
                        [['Vrai', true], ['Faux', false]],
                    ],
                    [
                        'On peut transmettre le VIH en partageant un repas.',
                        "Le VIH ne se transmet pas par les gestes de la vie quotidienne (repas, poignée de main, embrassade) mais par certains fluides corporels.",
                        [['Vrai', false], ['Faux', true]],
                    ],
                    [
                        'Une personne séropositive bien suivie médicalement peut avoir une vie longue et active.',
                        "Avec un traitement antirétroviral bien suivi, une personne séropositive peut vivre en bonne santé pendant de nombreuses années.",
                        [['Vrai', true], ['Faux', false]],
                    ],
                    [
                        'Le préservatif diminue fortement le risque de transmission des IST/VIH.',
                        "Utilisé correctement à chaque rapport, le préservatif reste le moyen de protection le plus efficace contre les IST, dont le VIH.",
                        [['Vrai', true], ['Faux', false]],
                    ],
                ],
            ],
            [
                'Le consentement, tu en sais quoi ?',
                "Un quiz pour mieux comprendre ce qu'est le consentement dans une relation.",
                ['Consentement', 'Genre et égalité'],
                [
                    [
                        'Le silence signifie-t-il un consentement ?',
                        "Le consentement doit être exprimé clairement et librement : l'absence de refus n'équivaut pas à un accord.",
                        [['Oui', false], ['Non', true], ['Seulement si la personne se tait longtemps', false], ['Cela dépend uniquement du contexte', false]],
                    ],
                    [
                        "Peut-on changer d'avis après avoir dit oui ?",
                        "Le consentement est réversible : chacun·e peut changer d'avis à tout moment, et cela doit être respecté immédiatement.",
                        [['Oui, à tout moment', true], ["Non, une fois dit c'est dit", false], ['Seulement avant que ça commence', false], ["Seulement si l'autre personne est d'accord", false]],
                    ],
                    [
                        'Être en couple oblige-t-il à accepter tous les rapports sexuels proposés par son ou sa partenaire ?',
                        "Être en couple ne supprime jamais le droit de dire non : le consentement doit être renouvelé à chaque fois.",
                        [['Oui', false], ['Non, jamais', true], ['Seulement au bout de plusieurs mois', false], ['Cela dépend des habitudes du couple', false]],
                    ],
                    [
                        'Peut-on donner son consentement sous la pression ou la menace ?',
                        "Un accord obtenu par pression, menace ou manipulation n'est pas un consentement libre.",
                        [['Oui, si la personne finit par accepter', false], ["Non, ce n'est pas un vrai consentement", true], ['Seulement si la pression est légère', false], ['Cela dépend de qui exerce la pression', false]],
                    ],
                ],
            ],
        ];

        $ordreQuiz = 0;
        foreach ($definitions as [$titre, $description, $nomsThematiques, $questions]) {
            $quiz = new Quiz();
            $quiz->setTitre($titre)->setDescription($description)
                ->setActif(true)->setOrdre($ordreQuiz++);

            foreach ($nomsThematiques as $nomThematique) {
                $quiz->addThematique($thematiques[$nomThematique]);
            }

            $ordreQuestion = 0;
            foreach ($questions as [$enonce, $explication, $choix]) {
                $question = new QuizQuestion();
                $question->setEnonce($enonce)->setExplication($explication)->setOrdre($ordreQuestion++);

                $ordreChoix = 0;
                foreach ($choix as [$libelle, $estCorrect]) {
                    $reponse = new QuizChoix();
                    $reponse->setLibelle($libelle)->setEstCorrect($estCorrect)->setOrdre($ordreChoix++);
                    $question->addChoix($reponse);
                }

                $quiz->addQuestion($question);
            }

            $manager->persist($quiz);
        }
    }

    private function contenuArticle(string $titre, string $resume): string
    {
        return <<<HTML
            <p><strong>{$resume}</strong></p>

            <p>Ce contenu s'inscrit dans l'action quotidienne du programme Amour&Vie ABMS auprès des jeunes béninois·es,
            portée depuis 1996 par PSI Bénin / ABMS autour d'une conviction simple : les jeunes sont les mieux placé·es
            pour parler aux jeunes de santé sexuelle et reproductive.</p>

            <h2>Une approche construite avec les jeunes</h2>

            <p>Qu'il s'agisse d'un Centre Jeunes, d'un numéro du magazine, d'une émission radio ou d'une séance de
            pair-éducation, chaque activité vise le même objectif : offrir aux 15-24 ans une information fiable,
            un espace d'écoute sans jugement, et des repères pour des choix éclairés.</p>

            <p>Les jeunes ayant participé soulignent l'importance de pouvoir poser toutes leurs questions librement,
            et la nécessité de maintenir ces espaces de dialogue dans la durée.</p>

            <blockquote>
                <p>Un programme des jeunes, pour les jeunes : c'est en écoutant celles et ceux à qui l'on s'adresse
                que l'on construit une information vraiment utile.</p>
            </blockquote>

            <h2>Les prochaines étapes</h2>

            <p>Le suivi de cette activité permettra d'évaluer son impact sur les prochains mois et d'ajuster les
            contenus proposés aux jeunes. Les enseignements seront partagés avec l'ensemble des équipes du programme
            et de ses partenaires.</p>

            <p><em>Ce contenu est un texte de démonstration. Il doit être remplacé par le contenu rédactionnel
            validé par Amour&Vie ABMS avant la mise en production.</em></p>
            HTML;
    }

    private function texteMentionsLegales(): string
    {
        return <<<'HTML'
            <h2>Éditeur du site</h2>
            <p>Le présent site est édité par <strong>Amour&Vie ABMS</strong>, programme de santé sexuelle et
            reproductive destiné aux jeunes, porté par l'Association Béninoise pour le Marketing Social (ABMS).</p>
            <p>Siège : 08 BP 0876 Tri postal Cotonou, République du Bénin.<br>
            Adresse électronique : contact@amouretvie.bj</p>

            <h2>Directeur de la publication</h2>
            <p>Le coordonnateur ou la coordonnatrice national·e du programme Amour&Vie ABMS.</p>

            <h2>Hébergement</h2>
            <p>Les coordonnées de l'hébergeur sont à compléter lors de la mise en production.</p>

            <h2>Propriété intellectuelle</h2>
            <p>L'ensemble des contenus présents sur ce site (textes, images, documents téléchargeables) est
            la propriété d'Amour&Vie ABMS, sauf mention contraire. Toute reproduction, même partielle, est
            soumise à autorisation préalable.</p>

            <p><em>Ce texte est un modèle à faire valider juridiquement avant publication.</em></p>
            HTML;
    }

    private function textePolitiqueConfidentialite(): string
    {
        return <<<'HTML'
            <h2>Données collectées</h2>
            <p>Selon les formulaires utilisés, ce site peut collecter votre prénom, votre adresse électronique
            (formulaire de contact, inscription à la newsletter) ou un pseudonyme facultatif (témoignage, question
            anonyme). Aucun formulaire public ne demande de nom de famille ni de document d'identité. Votre adresse
            IP est enregistrée à des fins de prévention des envois automatisés.</p>

            <h2>Finalité du traitement</h2>
            <p>Ces données sont utilisées exclusivement pour traiter votre demande, publier un témoignage ou une
            question après modération, ou vous adresser la newsletter si vous y êtes abonné·e. Elles ne font
            l'objet d'aucune cession à des tiers ni d'aucune exploitation commerciale.</p>

            <h2>Anonymat des questions et témoignages</h2>
            <p>Les questions posées via l'espace jeunes et les témoignages soumis ne comportent aucun champ e-mail
            ni identifiant permettant de retrouver leur auteur·rice : ils sont traités de manière anonyme.</p>

            <h2>Durée de conservation</h2>
            <p>Les messages et contenus soumis sont conservés le temps nécessaire à leur traitement, puis archivés
            conformément aux obligations du programme.</p>

            <h2>Vos droits</h2>
            <p>Vous disposez d'un droit d'accès, de rectification et de suppression des données vous concernant.
            Pour l'exercer, écrivez à contact@amouretvie.bj.</p>

            <h2>Cookies</h2>
            <p>Ce site n'utilise que les cookies strictement nécessaires à son fonctionnement (session et
            protection contre la falsification de requêtes). Aucun cookie publicitaire n'est déposé.</p>

            <p><em>Ce texte est un modèle à faire valider juridiquement avant publication.</em></p>
            HTML;
    }

    private function texteConditionsUtilisation(): string
    {
        return <<<'HTML'
            <h2>Objet</h2>
            <p>Les présentes conditions régissent l'utilisation du site institutionnel du programme Amour&Vie ABMS.
            La consultation du site vaut acceptation de ces conditions.</p>

            <h2>Utilisation des contenus</h2>
            <p>Les publications mises à disposition en téléchargement peuvent être consultées, citées et
            diffusées à des fins non commerciales, sous réserve de mentionner la source.</p>

            <h2>Espace jeunes et anonymat</h2>
            <p>Les questions, témoignages et échanges soumis via l'espace jeunes sont traités de manière
            confidentielle et modérés avant toute publication éventuelle. Amour&Vie ABMS se réserve le droit de ne
            pas publier un contenu jugé inapproprié, hors sujet ou contraire à la bienveillance attendue sur le site.</p>

            <h2>Disponibilité du service</h2>
            <p>Amour&Vie ABMS s'efforce d'assurer l'accessibilité permanente du site, sans pouvoir la garantir.
            Des interruptions peuvent survenir, notamment pour des opérations de maintenance.</p>

            <h2>Liens externes</h2>
            <p>Le site peut renvoyer vers des sites tiers dont Amour&Vie ABMS ne maîtrise pas le contenu et ne
            saurait être tenu responsable.</p>

            <p><em>Ce texte est un modèle à faire valider juridiquement avant publication.</em></p>
            HTML;
    }
}
