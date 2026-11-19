/**
 * Montage des îlots Vue.js du site public Amour&Vie.
 *
 * Les pages restent rendues par Symfony (Twig) : seuls les blocs réellement
 * interactifs sont confiés à Vue. Un îlot se déclare dans un template par :
 *
 *     <div data-vue="quiz" data-props="{{ {questions: ...}|json_encode|e('html_attr') }}">
 *         … contenu de repli rendu par Twig …
 *     </div>
 *
 * Le contenu de repli reste visible sans JavaScript (et lisible par les
 * moteurs de recherche) jusqu'au montage du composant, qui le remplace.
 * Vue et le composant ne sont téléchargés que si la page contient l'îlot.
 *
 * Avec la navigation instantanée, <main> est remplacé sans rechargement :
 * app.js appelle demonterIlots() avant et monterIlots() après chaque
 * remplacement.
 */

const composants = {
    'carte-centres-jeunes': () => import('./composants/CarteCentresJeunes.js'),
    quiz: () => import('./composants/Quiz.js'),
    'quiz-module': () => import('./composants/QuizModule.js'),
};

/** Applications Vue actives, pour pouvoir les démonter proprement. */
const applications = new Map();

async function monter(element) {
    const charger = composants[element.dataset.vue];
    if (!charger) {
        console.warn(`Îlot Vue inconnu : « ${element.dataset.vue} »`);
        return;
    }

    let props = {};
    try {
        props = JSON.parse(element.dataset.props || '{}');
    } catch (e) {
        console.error(`Propriétés invalides pour l'îlot « ${element.dataset.vue} »`, e);
        return;
    }

    const [{ createApp }, { default: composant }] = await Promise.all([
        import('./vue.js'),
        charger(),
    ]);

    // L'élément a pu quitter la page pendant le téléchargement.
    if (!element.isConnected) return;

    const application = createApp(composant, props);
    application.mount(element);
    applications.set(element, application);
    element.dataset.vueMonte = '';
}

export function monterIlots(racine = document) {
    racine.querySelectorAll('[data-vue]:not([data-vue-monte])').forEach((element) => {
        monter(element).catch((e) => console.error(`Échec du montage de l'îlot « ${element.dataset.vue} »`, e));
    });
}

export function demonterIlots(racine = document) {
    applications.forEach((application, element) => {
        if (racine.contains(element)) {
            application.unmount();
            applications.delete(element);
        }
    });
}
