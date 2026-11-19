# Plateforme Amour&Vie ABMS

Portail institutionnel et back-office **My Amour&Vie** pour le programme
béninois de santé sexuelle et reproductive des jeunes **Amour & Vie**
(initié par PSI Bénin / ABMS en 1996 — « programme des jeunes, pour les
jeunes »).

---

## 1. Architecture

| Composant | Choix retenu |
|-----------|--------------|
| Backend | Symfony 7.4 (PHP 8.2+) |
| Base de données | MySQL / MariaDB |
| Site public | Twig, rendu côté serveur |
| Back-office | EasyAdmin 5 |
| Interactivité | Vue.js 3 en îlots (carte des Centres Jeunes) + Stimulus (quiz), servis par AssetMapper |
| Téléversements | VichUploaderBundle |

Le site public est **rendu côté serveur** (Twig), et Vue.js/Stimulus ne sont
mobilisés que sur les blocs réellement interactifs (carte des Centres Jeunes,
quiz auto-corrigés). Cela garantit un référencement complet (chaque article,
publication et fiche centre est indexable) et un fonctionnement sur un
hébergement mutualisé classique, sans processus Node en production.

---

## 2. Installation en local (XAMPP)

### Prérequis

- PHP **8.2+** avec les extensions `pdo_mysql`, **`intl`**, **`gd`**, `mbstring`, `fileinfo`
- Composer 2
- MySQL ou MariaDB

> Les extensions `intl` et `gd` sont **obligatoires** : EasyAdmin utilise `intl` pour formater
> les dates, et la génération des certificats PDF (dompdf) utilise `gd` pour les images.
> Dans `C:\xampp\php\php.ini`, décommentez les lignes `extension=intl` et `extension=gd`,
> puis redémarrez Apache (ou le serveur Symfony).

### Étapes

```bash
composer install

# Adapter les identifiants de base dans .env.local
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Jeu de démonstration — à ne PAS charger en production
php bin/console doctrine:fixtures:load
```

Lancer le serveur de développement :

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

Le site est alors accessible sur <http://127.0.0.1:8000>.

### Via Apache (XAMPP)

Faire pointer un `VirtualHost` sur le dossier `public/` :

```apache
<VirtualHost *:80>
    ServerName amouretvie.local
    DocumentRoot "C:/xampp/htdocs/Amour&Vie/public"
    <Directory "C:/xampp/htdocs/Amour&Vie/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Le fichier `public/.htaccess` gère aussi une installation en sous-répertoire,
mais le `VirtualHost` reste préférable.

---

## 3. Accès au back-office

Le back-office **My Amour&Vie** est accessible sur `/admin`, la connexion sur `/connexion`.

### Comptes de démonstration

Créés par les fixtures — **à supprimer avant la mise en production**.

| Rôle | Identifiant | Mot de passe |
|------|-------------|--------------|
| Super administrateur | `admin@amouretvie.bj` | `AmourEtVie2026!` |
| Éditeur | `editeur@amouretvie.bj` | `AmourEtVie2026!` |
| Gestionnaire | `gestionnaire@amouretvie.bj` | `AmourEtVie2026!` |
| Contributeur | `contributeur@amouretvie.bj` | `AmourEtVie2026!` |

> Ces adresses sont fictives (domaine de démonstration), distinctes du vrai
> domaine `abmsbj.org` de l'organisation.

### Créer un compte réel

```bash
php bin/console app:creer-utilisateur
php bin/console app:creer-utilisateur contact@amouretvie.bj Kossi Adjovi --role=super-admin
```

Rôles disponibles : `super-admin`, `editeur`, `gestionnaire`, `contributeur`.

---

## 4. Rôles et permissions

| Rôle | Droits |
|------|--------|
| **Super administrateur** | Accès complet, y compris paramètres généraux et comptes |
| **Éditeur** | Rédige, valide et publie tous les articles ; supprime les contenus |
| **Gestionnaire** | Gère programmes, Centres Jeunes, émissions, équipe, partenaires, pages, taxonomies, témoignages, questions anonymes, newsletter, agenda |
| **Contributeur** | Rédige **ses** articles et dépose des médias ; ne peut pas publier |

Deux garde-fous complètent la hiérarchie des rôles :

- chaque contrôleur d'administration réservé porte un attribut `#[IsGranted]`,
  de sorte qu'une URL saisie directement soit refusée (403) et pas seulement
  masquée dans le menu ;
- `ArticleVoter` autorise un contributeur à ouvrir **uniquement ses propres
  articles**, y compris par accès direct à l'URL d'édition.

### Workflow éditorial

```
Contributeur  →  Brouillon  →  En attente de validation
                                        ↓
                       Éditeur / Super administrateur  →  Publié
```

Si un contributeur tente d'enregistrer un article en « Publié » ou « Programmé »,
le statut est automatiquement ramené à « En attente de validation » et un message
le lui signale. Le même principe de modération protège les **témoignages** et
les **questions anonymes** soumis par le site public : rien n'est visible
publiquement avant validation par l'équipe.

---

## 5. Contenus administrables

Tout est modifiable depuis My Amour&Vie, sans intervention technique :

- **Articles** — types, thématiques, programme, statut, mise à la une, programmation
- **Publications** — bibliothèque numérique (magazine, guides, rapports) avec compteur de téléchargements
- **Programmes** (« Nos actions ») — Centres Jeunes, Magazine, Émissions Radio & TV, Pair-éducation, Campagnes
- **Centres Jeunes** — annuaire + carte interactive (adresse, téléphone, horaires, géolocalisation)
- **Émissions** — épisodes radio/TV avec lecteur intégré (lien YouTube/SoundCloud)
- **Témoignages** — soumis anonymement par le site public, modérés avant publication
- **Questions anonymes / FAQ** — un·e jeune pose une question sans donner d'e-mail ; l'équipe répond en privé ou publie la réponse comme FAQ publique
- **Newsletter** — collecte des inscriptions (l'envoi de campagnes n'est pas encore branché : `MAILER_DSN` reste à configurer)
- **Quiz** — quiz pédagogiques auto-corrigés côté client, sans compte ni suivi individuel
- **Événements** — agenda public
- **Équipe**, **Partenaires**, **médiathèque**, **galeries**
- **Sections de page** — blocs de contenu des pages institutionnelles
- **Nos valeurs**, **chiffres clés**
- **Taxonomies** — thématiques, types d'article, types de publication
- **Paramètres généraux** — identité, bandeau d'accueil, coordonnées, réseaux
  sociaux (Facebook, Instagram, TikTok, YouTube, LinkedIn, X), pied de page, SEO et pages légales

### Sections de page

Une section est identifiée par un couple *page* + *clé technique*. Les gabarits
attendent notamment les clés suivantes :

| Page | Clés utilisées |
|------|----------------|
| Accueil | `presentation` |
| Qui sommes-nous ? | `histoire`, `mission`, `vision` |
| Espace jeunes | `intro` |
| Ressources | `intro` |

Une section dont la clé n'est pas reconnue est simplement ignorée : ajouter une
clé inconnue ne casse jamais l'affichage.

---

## 6. Accessibilité

- Structure sémantique et hiérarchie de titres respectée
- Lien d'évitement vers le contenu principal
- Anneau de focus visible et contrasté sur tous les éléments interactifs
- Textes alternatifs administrables sur chaque image
- Navigation entière au clavier, y compris menus, recherche, quiz et menu mobile
- Prise en compte de `prefers-reduced-motion` et `prefers-color-scheme`
- Carte des Centres Jeunes doublée d'un **équivalent textuel** dépliable
- Formulaires : erreurs reliées aux champs par `aria-describedby`, `role="alert"`
- Formulaires publics sensibles (témoignage, question anonyme) : anonymat par
  défaut, aucun champ obligatoire permettant l'identification

La protection CSRF utilise le mécanisme **par session** plutôt que la variante
« stateless » proposée par défaut : cette dernière fait calculer le jeton par
JavaScript, ce qui rendrait la connexion impossible sans JS.

---

## 7. Mise en production

```bash
# 1. Dépendances, sans les outils de développement
composer install --no-dev --optimize-autoloader

# 2. Environnement
#    Dans .env.local :
#      APP_ENV=prod
#      APP_SECRET=<chaîne aléatoire de 32 caractères>
#      DATABASE_URL=<connexion de production>
#      MAILER_DSN=<SMTP de l'organisation — newsletter, mots de passe oubliés et rappels e-learning>
#      MAILER_EXPEDITEUR="Amour&Vie <no-reply@domaine-reel>"
#      DEFAULT_URI=https://domaine-reel   (liens absolus des e-mails envoyés par les commandes)
composer dump-env prod

# 3. Base de données
php bin/console doctrine:migrations:migrate --no-interaction

# 4. Assets et cache — vider var/cache entièrement : le cache des métadonnées
#    de VichUploader (var/cache/<env>/vich_uploader) survit à cache:clear.
php bin/console asset-map:compile
rm -rf var/cache/*
php bin/console cache:warmup
```

Tâche planifiée (cron) pour les rappels de cours inachevés, une fois par jour :

```cron
0 9 * * * cd /chemin/du/site && php bin/console app:elearning:rappels --env=prod
```

### Points de vigilance

- **Ne jamais charger les fixtures en production** : elles purgent la base.
- Supprimer les comptes de démonstration ; créer les comptes réels avec
  `app:creer-utilisateur`.
- Le dossier `public/uploads/` doit être accessible en écriture par le serveur web.
- Activer **HTTPS** et forcer la redirection.
- Programmer une **sauvegarde automatique** de la base et des dossiers `public/uploads/`
  et `var/certificats/` (PDF des certificats ; regénérables depuis la base au besoin).
- Le dossier `var/certificats/` doit être accessible en écriture et **jamais servi
  publiquement** (il est hors de `public/`).
- **Chaque projet doit avoir sa propre base** : ne jamais faire pointer deux sites
  vers la même base (le chargement de fixtures de l'un effacerait l'autre).
- L'envoi effectif de la newsletter nécessite un `MAILER_DSN` réel (SMTP,
  Mailjet, Brevo…) — la collecte des inscriptions fonctionne sans, mais aucun
  e-mail n'est envoyé tant que ce n'est pas configuré.

### Contenus à remplacer avant la mise en ligne

Les fixtures fournissent des textes réalistes mais **provisoires**. À valider
avec l'équipe Amour&Vie ABMS :

- les adresses et coordonnées réelles des Centres Jeunes (villes correctes,
  adresses de rue et numéros de téléphone à confirmer) ;
- les liens réels vers les comptes Facebook/Instagram/TikTok/YouTube existants ;
- la composition réelle de l'équipe et du conseil d'administration ;
- les mentions légales, la politique de confidentialité et les conditions
  d'utilisation, qui doivent être **validées juridiquement** ;
- le logo, le favicon et les photographies ;
- les liens des émissions radio/TV (actuellement vides ou placeholders).
- le contenu du **cours pilote** de l'e-learning (démonstration à valider par l'ABMS),
  le nom, la fonction et la signature du signataire des certificats.

---

## 8. E-learning (/apprendre)

Plateforme d'apprentissage intégrée au site : catalogue de cours, leçons, quiz,
examen final et certificat vérifiable. Comptes des jeunes **séparés** de ceux de
l'équipe (pare-feu `apprendre` dans `config/packages/security.yaml`).

| Étape | Fonctionnement |
|-------|----------------|
| Inscription | Prénom, nom, e-mail, téléphone facultatif, consentement explicite. Connexion limitée à 5 essais/minute, mot de passe oublié par lien e-mail valable 1 h. |
| Cours | Cours → modules → leçons (texte, vidéo chargée à la demande). Progression et reprise enregistrées. |
| Quiz de module | Quiz de la rubrique « Quiz », **corrigé côté serveur** ; module validé à partir du seuil du cours. |
| Examen final | Facultatif, accessible une fois les modules validés, tentatives limitées (réglage du cours). Les bonnes réponses ne sont dévoilées qu'après réussite ou épuisement des tentatives. |
| Certificat | Délivré automatiquement après confirmation de l'orthographe du nom ; numéro aléatoire `AV-XXXX-XXXX-XXXX`, QR code, PDF (A4 paysage) conservé dans `var/certificats/`. |
| Vérification | Page publique `/verifier/{numero}` (non indexée) : authentique, révoqué ou introuvable. |

Vie privée : seuls les **scores** des quiz et examens sont conservés, jamais le
détail des réponses ; les statistiques sont agrégées ; l'export nominatif est
réservé aux éditeurs.

**Back-office** (section « E-learning ») : statistiques et export CSV (Excel),
cours, modules, leçons, apprenants (correction du nom, désactivation), suivi
des apprenants (redonner les tentatives d'examen), certificats (PDF, révocation,
réémission avec le même numéro). Le signataire et la signature des certificats
se règlent dans *Paramètres généraux → Certificats*.

Commandes :

```bash
# Crée le cours pilote de démonstration (non publié ; --publier pour les tests)
php bin/console app:elearning:cours-pilote

# Rappel par e-mail des cours inachevés depuis 7 jours (--simulation pour lister sans envoyer)
php bin/console app:elearning:rappels
```

---

## 9. Structure du code

```
src/
├── Command/            Commande de création de compte
├── Controller/         Contrôleurs du site public
│   └── Admin/          Contrôleurs EasyAdmin (My Amour&Vie)
├── DataFixtures/       Jeu de démonstration
├── Entity/              Entités Doctrine (Article, Programme, CentreJeune,
│                        Emission, Temoignage, QuestionAnonyme,
│                        NewsletterAbonne, Quiz…)
├── Enum/                Statuts, catégories, rôles
├── Form/                Formulaires (contact, newsletter, témoignage, question anonyme, quiz)
├── Repository/          Requêtes métier et filtres
├── Security/Voter/      Contrôle d'accès article par article
├── Service/             Contexte global du site
└── Twig/                Icônes SVG et filtres de formatage

templates/
├── accueil/ actualite/ page/ programme/ publication/ evenement/
├── centre_jeune/ emission/ temoignage/ question_anonyme/ quiz/
├── espace_jeunes/ ressources/
├── admin/               Tableau de bord
├── form/                Thème de formulaire
├── partials/            En-tête, pied de page, cartes, pagination, sprite
└── securite/            Écran de connexion

assets/
├── app.js                    Interactions du site public (dont bascule thème sombre)
├── carte-centres-jeunes.js   Îlot Vue.js de la carte des Centres Jeunes
├── controllers/              Contrôleurs Stimulus (quiz)
└── styles/app.css            Système de design
```

---

## 10. Charte graphique

| Rôle | Couleur |
|------|---------|
| Rose/corail (primaire) | `#E85D75` |
| Vert émeraude (secondaire) | `#1E9E6D` |
| Violet (accent) | `#8B5CF6` |
| Gris | échelle chaleureuse de `#FFFFFF` à `#1A1513` |

Les couleurs sont centralisées dans les variables CSS en tête de
`assets/styles/app.css` : les modifier suffit à propager le changement à
l'ensemble du site. Le mode sombre redéfinit uniquement les alias sémantiques
(`--fond`, `--texte`, `--bordure`, `--surface`…), jamais les couleurs de
marque — boutons et accents restent identiques dans les deux thèmes.
