/**
 * Navigation instantanée du site public Amour&Vie.
 *
 * Les pages restent produites par Symfony/Twig (référencement, accessibilité
 * sans JavaScript) ; ce module évite seulement le rechargement complet :
 *
 *  - au survol (ou au toucher) d'un lien interne, la page cible est
 *    préchargée en arrière-plan ;
 *  - au clic, seul <main> est remplacé, avec le titre, les métadonnées et le
 *    lien actif du menu ; en-tête, pied de page et thème restent en place ;
 *  - l'historique (précédent/suivant) et la position de défilement sont
 *    conservés ;
 *  - au moindre doute (lien externe, fichier, formulaire POST, erreur
 *    réseau, réponse non HTML), on retombe sur une navigation classique.
 *
 * Un lien ou une zone peut forcer la navigation classique avec l'attribut
 * data-navigation-complete.
 */

const SELECTEUR_CONTENU = '#contenu-principal';

/**
 * Chemins toujours servis par une navigation classique (admin, fichiers,
 * déconnexions : l'état de connexion doit être relu entièrement).
 */
const CHEMINS_EXCLUS = /^\/(admin|connexion|deconnexion|apprendre\/deconnexion|uploads|media|assets|_)(\/|$)/;

/** Un chemin terminé par une extension désigne un fichier, pas une page. */
const EXTENSION_FICHIER = /\.[a-z0-9]{2,5}$/i;

/** Métadonnées de <head> resynchronisées à chaque page. */
const SELECTEUR_METAS = [
    'meta[name="description"]',
    'meta[name="robots"]',
    'link[rel="canonical"]',
    'meta[property^="og:"]',
    'meta[name^="twitter:"]',
    'script[type="application/ld+json"]',
].join(',');

const DUREE_CACHE = 30_000;
const DELAI_SURVOL = 65;

const cache = new Map();
let navigationEnCours = null;
/** Chemin + paramètres du document actuellement affiché. */
let adresseAffichee = window.location.pathname + window.location.search;
let rappels = { avantRemplacement: () => {}, apresRemplacement: () => {} };

function urlEligible(url) {
    return url.origin === window.location.origin
        && !CHEMINS_EXCLUS.test(url.pathname)
        && !EXTENSION_FICHIER.test(url.pathname);
}

function lienEligible(lien) {
    if (!lien || !lien.href || lien.hasAttribute('download')) return false;
    if (lien.target && lien.target !== '_self') return false;
    if (lien.closest('[data-navigation-complete]')) return false;

    const url = new URL(lien.href, window.location.href);
    if (!urlEligible(url)) return false;

    // Ancre dans la même page : le navigateur s'en charge très bien.
    const memePage = url.pathname === window.location.pathname && url.search === window.location.search;
    return !(memePage && url.hash);
}

/** Clé de cache : l'ancre ne change pas le document renvoyé. */
const cleCache = (url) => url.href.split('#')[0];

function precharger(url) {
    const cle = cleCache(url);
    const entree = cache.get(cle);
    if (entree && Date.now() - entree.date < DUREE_CACHE) {
        return entree.promesse;
    }

    const promesse = fetch(cle, {
        credentials: 'same-origin',
        headers: { Accept: 'text/html', 'X-Prechargement': '1' },
    }).then(async (reponse) => ({
        ok: reponse.ok,
        html: (reponse.headers.get('Content-Type') || '').includes('text/html'),
        url: reponse.url,
        visiteAConfirmer: reponse.headers.has('X-Visite-A-Confirmer'),
        texte: await reponse.text(),
    }));

    // Une erreur réseau ne doit pas rester en cache.
    promesse.catch(() => cache.delete(cle));
    cache.set(cle, { promesse, date: Date.now() });

    return promesse;
}

function synchroniserTete(nouveauDocument) {
    document.title = nouveauDocument.title;
    document.head.querySelectorAll(SELECTEUR_METAS).forEach((el) => el.remove());
    nouveauDocument.head.querySelectorAll(SELECTEUR_METAS).forEach((el) => {
        document.head.appendChild(document.importNode(el, true));
    });
}

/**
 * Le menu est identique sur toutes les pages, seul le lien actif change :
 * on recopie les classes et aria-current lien par lien.
 */
function synchroniserMenus(nouveauDocument) {
    ['[data-entete]', '[data-menu-mobile]'].forEach((selecteur) => {
        const actuels = document.querySelectorAll(`${selecteur} a`);
        const nouveaux = nouveauDocument.querySelectorAll(`${selecteur} a`);
        if (actuels.length !== nouveaux.length) return;

        actuels.forEach((lien, i) => {
            lien.className = nouveaux[i].className;
            if (nouveaux[i].hasAttribute('aria-current')) {
                lien.setAttribute('aria-current', nouveaux[i].getAttribute('aria-current'));
            } else {
                lien.removeAttribute('aria-current');
            }
        });
    });
}

/** Les <script> insérés via le DOM ne s'exécutent pas : on les recrée. */
function reactiverScripts(racine) {
    racine.querySelectorAll('script').forEach((ancien) => {
        if (ancien.type === 'application/ld+json') return;
        const script = document.createElement('script');
        Array.from(ancien.attributes).forEach((attr) => script.setAttribute(attr.name, attr.value));
        script.textContent = ancien.textContent;
        ancien.replaceWith(script);
    });
}

function annoncer(titre) {
    let region = document.getElementById('annonce-navigation');
    if (!region) {
        region = document.createElement('div');
        region.id = 'annonce-navigation';
        region.className = 'sr-only';
        region.setAttribute('aria-live', 'polite');
        document.body.appendChild(region);
    }
    region.textContent = '';
    // Laisse aux lecteurs d'écran le temps de constater le changement.
    setTimeout(() => { region.textContent = titre; }, 50);
}

function defiler(url, positionY) {
    if (positionY !== undefined) {
        window.scrollTo(0, positionY);
        return;
    }
    const cible = url.hash && document.getElementById(decodeURIComponent(url.hash.slice(1)));
    if (cible) {
        cible.scrollIntoView();
    } else {
        window.scrollTo(0, 0);
    }
}

function basculerChargement(actif) {
    document.documentElement.classList.toggle('navigation-en-cours', actif);
}

function remplacerContenu(nouveauDocument) {
    const actuel = document.querySelector(SELECTEUR_CONTENU);
    const nouveau = nouveauDocument.querySelector(SELECTEUR_CONTENU);

    rappels.avantRemplacement(actuel);

    const contenu = document.importNode(nouveau, true);
    actuel.replaceWith(contenu);
    document.body.className = nouveauDocument.body.className;
    synchroniserTete(nouveauDocument);
    synchroniserMenus(nouveauDocument);
    reactiverScripts(contenu);

    rappels.apresRemplacement(contenu);
    return contenu;
}

/**
 * @param {URL} url
 * @param {{historique?: 'ajouter'|'remplacer'|null, positionY?: number}} options
 */
async function naviguer(url, { historique = 'ajouter', positionY } = {}) {
    const jeton = {};
    navigationEnCours = jeton;
    basculerChargement(true);

    let reponse;
    try {
        const cle = cleCache(url);
        const dejaPrechargee = cache.has(cle);
        reponse = await precharger(url);

        // La page avait été préchargée sans compter la visite (ex. vues d'un
        // article) : maintenant que le visiteur l'ouvre, on la confirme.
        if (dejaPrechargee && reponse.visiteAConfirmer) {
            fetch(cle, { method: 'HEAD', credentials: 'same-origin', keepalive: true }).catch(() => {});
        }
        // Une page n'est consommée qu'une fois : la prochaine visite la redemandera.
        cache.delete(cle);
    } catch (e) {
        window.location.assign(url.href);
        return;
    }

    // Un clic plus récent a pris le relais.
    if (navigationEnCours !== jeton) return;

    const urlFinale = new URL(reponse.url || url.href);
    if (!reponse.ok || !reponse.html || !urlEligible(urlFinale)) {
        window.location.assign(url.href);
        return;
    }

    const nouveauDocument = new DOMParser().parseFromString(reponse.texte, 'text/html');
    if (!nouveauDocument.querySelector(SELECTEUR_CONTENU)) {
        window.location.assign(url.href);
        return;
    }

    // Une redirection côté serveur conserve l'ancre demandée.
    urlFinale.hash = url.hash;

    if (historique === 'ajouter') {
        history.replaceState({ ...history.state, positionY: window.scrollY }, '');
        history.pushState({ navigationInstantanee: true }, '', urlFinale.href);
    } else if (historique === 'remplacer') {
        history.replaceState({ navigationInstantanee: true }, '', urlFinale.href);
    }

    adresseAffichee = urlFinale.pathname + urlFinale.search;

    const appliquer = () => {
        const contenu = remplacerContenu(nouveauDocument);
        defiler(urlFinale, positionY);
        return contenu;
    };

    const reduitMouvement = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let contenu;
    if (document.startViewTransition && !reduitMouvement) {
        const transition = document.startViewTransition(() => { contenu = appliquer(); });
        await transition.updateCallbackDone;
    } else {
        contenu = appliquer();
    }

    basculerChargement(false);

    // Focus sur le nouveau contenu pour les utilisateurs clavier / lecteurs d'écran.
    const titre = contenu.querySelector('h1');
    const cibleFocus = titre || contenu;
    if (!cibleFocus.hasAttribute('tabindex')) cibleFocus.setAttribute('tabindex', '-1');
    cibleFocus.focus({ preventScroll: true });
    annoncer(document.title);

    document.dispatchEvent(new CustomEvent('navigation:chargee', { detail: { url: urlFinale } }));
}

function surClic(e) {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

    const lien = e.target.closest('a');
    if (!lienEligible(lien)) return;

    e.preventDefault();
    naviguer(new URL(lien.href, window.location.href));
}

/** Formulaires GET (filtres, recherche) : même traitement qu'un lien. */
function surSoumission(e) {
    const formulaire = e.target;
    if (e.defaultPrevented || formulaire.method.toLowerCase() !== 'get') return;
    if (formulaire.target && formulaire.target !== '_self') return;
    if (formulaire.closest('[data-navigation-complete]')) return;

    const url = new URL(formulaire.action || window.location.href, window.location.href);
    if (!urlEligible(url)) return;

    e.preventDefault();
    const donnees = e.submitter ? new FormData(formulaire, e.submitter) : new FormData(formulaire);
    url.search = new URLSearchParams(donnees).toString();
    url.hash = '';

    // Fermer une éventuelle surcouche (recherche, menu mobile) avant de partir.
    formulaire.closest('[data-recherche]')?.querySelector('[data-ferme-recherche]')?.click();

    naviguer(url);
}

let minuteurSurvol = null;

function surSurvol(e) {
    const lien = e.target.closest?.('a');
    if (!lienEligible(lien)) return;

    clearTimeout(minuteurSurvol);
    minuteurSurvol = setTimeout(() => {
        precharger(new URL(lien.href, window.location.href)).catch(() => {});
    }, DELAI_SURVOL);
}

function surToucher(e) {
    const lien = e.target.closest?.('a');
    if (lienEligible(lien)) {
        precharger(new URL(lien.href, window.location.href)).catch(() => {});
    }
}

function surRetour(e) {
    const url = new URL(window.location.href);

    // Simple changement d'ancre dans la même page : le navigateur s'en charge.
    if (url.pathname + url.search === adresseAffichee) return;

    naviguer(url, { historique: null, positionY: e.state?.positionY ?? 0 });
}

/**
 * @param {{avantRemplacement?: (main: Element) => void, apresRemplacement?: (main: Element) => void}} options
 *   Appelés juste avant et juste après le remplacement de <main> : c'est là
 *   qu'on démonte et remonte les comportements propres à la page.
 */
export function initNavigationInstantanee(options = {}) {
    if (!window.fetch || !window.DOMParser || !history.pushState) return;

    rappels = { ...rappels, ...options };
    document.documentElement.dataset.navigationInstantanee = '';
    history.scrollRestoration = 'manual';
    history.replaceState({ ...history.state, navigationInstantanee: true }, '');

    document.addEventListener('click', surClic);
    document.addEventListener('submit', surSoumission);
    document.addEventListener('mouseover', surSurvol, { passive: true });
    document.addEventListener('mouseout', () => clearTimeout(minuteurSurvol), { passive: true });
    document.addEventListener('touchstart', surToucher, { passive: true });
    window.addEventListener('popstate', surRetour);
}
