import './styles/app.css';
import { initNavigationInstantanee } from './navigation-instantanee.js';
import { monterIlots, demonterIlots } from './vue/ilots.js';

/**
 * Interactions du site public Amour&Vie.
 *
 * Tout est piloté au clavier autant qu'à la souris : ouverture/fermeture
 * par Échap, focus déplacé vers l'élément pertinent, attributs aria tenus
 * à jour (RGAA / WCAG 2.1).
 */

/**
 * Bascule du thème clair/sombre. La lecture initiale de localStorage se
 * fait de façon synchrone dans <head> (base.html.twig) pour éviter un
 * flash de mauvais thème ; ce module ne gère que le clic sur le bouton.
 */
function initTheme() {
    const bouton = document.querySelector('[data-bascule-theme]');
    if (!bouton) return;

    const estSombre = () => document.documentElement.dataset.theme === 'dark'
        || (!('theme' in document.documentElement.dataset)
            && window.matchMedia('(prefers-color-scheme: dark)').matches);

    const majLibelle = () => {
        bouton.setAttribute('aria-label', estSombre() ? 'Activer le thème clair' : 'Activer le thème sombre');
    };
    majLibelle();

    bouton.addEventListener('click', () => {
        const passerAuSombre = !estSombre();
        document.documentElement.dataset.theme = passerAuSombre ? 'dark' : 'light';
        try {
            localStorage.setItem('theme', passerAuSombre ? 'sombre' : 'clair');
        } catch (e) { /* stockage indisponible (navigation privée) : rien à faire */ }
        majLibelle();
    });
}

/**
 * Ombre portée dès que la page défile, et repli de la bande de
 * coordonnées (bande blanche) pour ne garder que la bande rouge sticky.
 */
function initEntete() {
    const entete = document.querySelector('[data-entete]');
    if (!entete) return;

    const majEtat = () => {
        entete.classList.toggle('est-flottante', window.scrollY > 12);
        entete.classList.toggle('est-replie', window.scrollY > 60);
    };
    majEtat();
    window.addEventListener('scroll', majEtat, { passive: true });
}

/** Recherche globale en surcouche. */
function initRecherche() {
    const overlay = document.querySelector('[data-recherche]');
    const ouvrir = document.querySelector('[data-ouvre-recherche]');
    const fermer = document.querySelector('[data-ferme-recherche]');
    const champ = document.querySelector('[data-champ-recherche]');
    if (!overlay || !ouvrir) return;

    const basculer = (ouverte) => {
        overlay.classList.toggle('est-ouverte', ouverte);
        ouvrir.setAttribute('aria-expanded', String(ouverte));
        document.body.style.overflow = ouverte ? 'hidden' : '';
        if (ouverte) {
            champ?.focus();
        } else {
            ouvrir.focus();
        }
    };

    ouvrir.addEventListener('click', () => basculer(true));
    fermer?.addEventListener('click', () => basculer(false));

    // Clic sur le fond, en dehors de la boîte de dialogue.
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) basculer(false);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('est-ouverte')) basculer(false);

        // Raccourci « / » pour ouvrir la recherche, hors champ de saisie.
        const dansUnChamp = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName);
        if (e.key === '/' && !dansUnChamp && !overlay.classList.contains('est-ouverte')) {
            e.preventDefault();
            basculer(true);
        }
    });
}

/** Menu de navigation mobile. */
function initMenuMobile() {
    const menu = document.querySelector('[data-menu-mobile]');
    const ouvrir = document.querySelector('[data-ouvre-menu]');
    const fermer = document.querySelector('[data-ferme-menu]');
    if (!menu || !ouvrir) return;

    const basculer = (ouvert) => {
        menu.classList.toggle('est-ouvert', ouvert);
        ouvrir.setAttribute('aria-expanded', String(ouvert));
        document.body.style.overflow = ouvert ? 'hidden' : '';
        if (ouvert) {
            menu.querySelector('a, button')?.focus();
        } else {
            ouvrir.focus();
        }
    };

    ouvrir.addEventListener('click', () => basculer(true));
    fermer?.addEventListener('click', () => basculer(false));

    menu.addEventListener('click', (e) => {
        if (e.target === menu) basculer(false);
        // Un clic sur un lien ferme le menu et laisse la navigation se faire.
        if (e.target.closest('a')) basculer(false);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && menu.classList.contains('est-ouvert')) basculer(false);
    });

    // Accordéons des sous-menus.
    menu.querySelectorAll('[data-bascule-sous-menu]').forEach((bouton) => {
        bouton.addEventListener('click', () => {
            const sousListe = bouton.nextElementSibling;
            const ouvert = bouton.getAttribute('aria-expanded') === 'true';
            bouton.setAttribute('aria-expanded', String(!ouvert));
            sousListe?.classList.toggle('est-ouverte', !ouvert);
        });
    });
}

/** Bouton de retour en haut de page. */
function initRetourHaut() {
    const bouton = document.querySelector('[data-retour-haut]');
    if (!bouton) return;

    const majVisibilite = () => bouton.classList.toggle('est-visible', window.scrollY > 500);
    majVisibilite();
    window.addEventListener('scroll', majVisibilite, { passive: true });

    bouton.addEventListener('click', () => {
        const doux = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        window.scrollTo({ top: 0, behavior: doux ? 'smooth' : 'auto' });
    });
}

/**
 * Compteur animé des chiffres clés de l'accueil.
 * Se déclenche à l'entrée dans le viewport et respecte le mouvement réduit.
 */
function initCompteurs(racine) {
    const compteurs = racine.querySelectorAll('[data-compteur]');
    if (compteurs.length === 0) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        compteurs.forEach((el) => { el.textContent = el.dataset.compteur; });
        return;
    }

    const animer = (el) => {
        const cible = parseInt(el.dataset.compteur, 10) || 0;
        const duree = 1400;
        const depart = performance.now();

        const etape = (maintenant) => {
            const progression = Math.min((maintenant - depart) / duree, 1);
            // Courbe d'atténuation : démarrage rapide, arrivée douce.
            const valeur = Math.round(cible * (1 - Math.pow(1 - progression, 3)));
            el.textContent = valeur.toLocaleString('fr-FR');
            if (progression < 1) requestAnimationFrame(etape);
        };

        requestAnimationFrame(etape);
    };

    const observateur = new IntersectionObserver((entrees) => {
        entrees.forEach((entree) => {
            if (entree.isIntersecting) {
                animer(entree.target);
                observateur.unobserve(entree.target);
            }
        });
    }, { threshold: 0.4 });

    compteurs.forEach((el) => observateur.observe(el));

    return () => observateur.disconnect();
}

/**
 * Émission radio : révèle le lecteur audio natif au clic sur la couverture,
 * et démarre la lecture.
 */
function initLecteursAudio(racine) {
    racine.querySelectorAll('[data-lecteur-audio]').forEach((conteneur) => {
        const bouton = conteneur.querySelector('[data-lecteur-audio-bouton]');
        const lecteur = conteneur.querySelector('[data-lecteur-audio-element]');
        if (!bouton || !lecteur) return;

        bouton.addEventListener('click', () => {
            bouton.hidden = true;
            lecteur.hidden = false;
            lecteur.play().catch(() => { /* lecture auto bloquée : le visiteur peut cliquer sur play */ });
        });
    });
}

/**
 * Vidéo de leçon (e-learning) chargée seulement au clic : sur un petit
 * forfait mobile, le texte de la leçon suffit et rien n'est téléchargé.
 */
function initVideosDifferees(racine) {
    racine.querySelectorAll('[data-video-differee][data-video-src]').forEach((conteneur) => {
        const bouton = conteneur.querySelector('[data-video-bouton]');
        if (!bouton) return;

        bouton.addEventListener('click', () => {
            const video = document.createElement('iframe');
            video.src = `${conteneur.dataset.videoSrc}?autoplay=1`;
            video.title = 'Vidéo de la leçon';
            video.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
            video.allowFullscreen = true;
            bouton.replaceWith(video);
            video.focus();
        });
    });
}

/** Soumission automatique des filtres au changement d'un select. */
function initFiltres(racine) {
    racine.querySelectorAll('[data-filtre-auto]').forEach((formulaire) => {
        formulaire.querySelectorAll('select').forEach((select) => {
            // requestSubmit() déclenche l'événement submit, que la navigation
            // instantanée intercepte (submit() le court-circuiterait).
            select.addEventListener('change', () => formulaire.requestSubmit());
        });
    });
}

/**
 * Replie les champs de filtre derrière un bouton « Filtrer », pour ne pas
 * surcharger la page. Le bouton peut vivre n'importe où dans la page (ex.
 * dans la bannière) : il vise son panneau via aria-controls. Le panneau
 * s'ouvre automatiquement si des filtres sont déjà actifs (venant de
 * l'URL), afin de ne jamais les cacher.
 */
function initFiltresRepliables(racine) {
    racine.querySelectorAll('[data-bascule-filtres]').forEach((bouton) => {
        const corps = document.getElementById(bouton.getAttribute('aria-controls'));
        if (!corps) return;

        const aDesValeurs = Array.from(corps.querySelectorAll('select, input[type="search"]'))
            .some((champ) => champ.value);

        if (aDesValeurs) {
            corps.hidden = false;
            bouton.setAttribute('aria-expanded', 'true');
        }

        bouton.addEventListener('click', () => {
            const ouvert = bouton.getAttribute('aria-expanded') === 'true';
            corps.hidden = ouvert;
            bouton.setAttribute('aria-expanded', String(!ouvert));
        });
    });
}

/**
 * Carrousel du hero d'accueil : défilement automatique gauche-droite entre
 * les derniers articles publiés, avec puces de navigation. Se met en pause
 * au survol/focus et respecte le mouvement réduit.
 */
function initHeroCarousel(racine) {
    const hero = racine.querySelector('[data-carousel]');
    const piste = hero?.querySelector('[data-carousel-piste]');
    const diapos = hero ? Array.from(hero.querySelectorAll('[data-carousel-diapo]')) : [];
    const puces = hero ? Array.from(hero.querySelectorAll('[data-carousel-puce]')) : [];

    if (!hero || !piste || diapos.length <= 1) return;

    const reduitMouvement = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let index = 0;
    let minuteur = null;

    const allerA = (cible) => {
        index = (cible + diapos.length) % diapos.length;
        piste.style.transform = `translateX(-${index * 100}%)`;

        diapos.forEach((diapo, i) => {
            const actif = i === index;
            diapo.setAttribute('aria-hidden', actif ? 'false' : 'true');
            // Une diapositive hors champ ne doit pas être atteignable au clavier.
            diapo.querySelectorAll('a, button').forEach((el) => {
                el.tabIndex = actif ? 0 : -1;
            });
        });

        puces.forEach((puce, i) => {
            puce.classList.toggle('est-active', i === index);
            puce.setAttribute('aria-current', i === index ? 'true' : 'false');
        });
    };

    const arreter = () => { if (minuteur) clearInterval(minuteur); };
    const demarrer = () => {
        if (reduitMouvement) return;
        arreter();
        minuteur = setInterval(() => allerA(index + 1), 6500);
    };

    puces.forEach((puce, i) => {
        puce.addEventListener('click', () => { allerA(i); demarrer(); });
    });

    hero.addEventListener('mouseenter', arreter);
    hero.addEventListener('mouseleave', demarrer);
    hero.addEventListener('focusin', arreter);
    hero.addEventListener('focusout', demarrer);

    allerA(0);
    demarrer();

    return arreter;
}

/**
 * Comportements propres au contenu de la page (<main>). Ils sont relancés à
 * chaque navigation instantanée, après avoir nettoyé ceux de la page
 * précédente (minuteurs, observateurs, applications Vue).
 */
let nettoyages = [];

function initContenu(racine) {
    nettoyages = [
        initCompteurs(racine),
        initLecteursAudio(racine),
        initVideosDifferees(racine),
        initFiltres(racine),
        initFiltresRepliables(racine),
        initHeroCarousel(racine),
    ].filter((nettoyage) => typeof nettoyage === 'function');

    monterIlots(racine);
}

function nettoyerContenu(racine) {
    nettoyages.forEach((nettoyer) => nettoyer());
    nettoyages = [];
    demonterIlots(racine);
}

document.addEventListener('DOMContentLoaded', () => {
    initEntete();
    initTheme();
    initRecherche();
    initMenuMobile();
    initRetourHaut();

    initContenu(document.querySelector('#contenu-principal') || document);

    initNavigationInstantanee({
        avantRemplacement: nettoyerContenu,
        apresRemplacement: initContenu,
    });
});
