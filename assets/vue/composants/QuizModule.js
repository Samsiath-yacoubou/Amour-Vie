import { ref, computed, nextTick } from '../vue.js';

/**
 * Îlot « quiz de module » de l'e-learning (templates/apprendre/quiz.html.twig).
 *
 * Contrairement au quiz public (Quiz.js), les bonnes réponses ne sont pas
 * envoyées au navigateur : le jeune répond à toutes les questions, valide,
 * et le serveur renvoie la correction, les explications et le score
 * (ApprendreController::corrigerQuiz). Seul le score est enregistré.
 */
export default {
    template: `
        <div class="quiz" ref="racine" tabindex="-1">
            <div class="quiz__progression" v-if="!resultat">
                <p class="quiz__compteur" aria-live="polite">
                    {{ nbRepondues }} / {{ questions.length }} question{{ questions.length > 1 ? 's' : '' }} répondue{{ nbRepondues > 1 ? 's' : '' }}
                </p>
                <div class="quiz__barre" role="progressbar" :aria-valuenow="nbRepondues"
                     aria-valuemin="0" :aria-valuemax="questions.length" aria-label="Questions répondues">
                    <span :style="{ width: (nbRepondues / questions.length * 100) + '%' }"></span>
                </div>
            </div>

            <div v-if="resultat" class="quiz__resultat" role="status" ref="blocResultat" tabindex="-1">
                <p class="quiz__score">{{ resultat.pourcentage }} %</p>
                <p v-if="resultat.coursTermine"><strong>Bravo, tu as terminé le cours !</strong>
                    <template v-if="examen"> Ton certificat t'attend.</template></p>
                <p v-else-if="resultat.reussi"><strong>{{ examen ? 'Examen réussi !' : 'Module validé !' }}</strong> {{ bilanTexte }}.</p>
                <template v-else>
                    <p>{{ bilanTexte }}. Il en faut {{ seuil }} % pour {{ examen ? 'réussir l\\'examen' : 'valider le module' }}.</p>
                    <p v-if="examen && epuise"><strong>Tu as utilisé toutes tes tentatives.</strong> Les bonnes réponses sont affichées ci-dessous.
                        Contacte l'équipe Amour&amp;Vie si tu souhaites en discuter.</p>
                    <p v-else-if="examen && resultat.tentativesRestantes !== null">
                        Il te reste {{ resultat.tentativesRestantes }} tentative{{ resultat.tentativesRestantes > 1 ? 's' : '' }} : revois les leçons des questions ratées avant de réessayer.</p>
                    <p v-else>Relis les explications ci-dessous et réessaie.</p>
                </template>
                <div class="quiz__actions">
                    <a v-if="resultat.reussi" class="bouton bouton--primaire" :href="resultat.suite">
                        {{ resultat.coursTermine ? (examen ? 'Obtenir mon certificat' : 'Voir mon cours') : 'Continuer le cours' }}
                    </a>
                    <a v-else-if="epuise" class="bouton bouton--contour" :href="urlCours">Retour au cours</a>
                    <button v-else type="button" class="bouton bouton--primaire" @click="recommencer">
                        {{ examen ? 'Repasser l\\'examen' : 'Réessayer le quiz' }}
                    </button>
                </div>
            </div>

            <fieldset v-for="(question, q) in questions" :key="question.id" class="quiz__question">
                <legend>
                    <span class="sr-only">Question {{ q + 1 }} sur {{ questions.length }} : </span>
                    {{ question.enonce }}
                </legend>

                <div class="puces" role="radiogroup" :aria-label="'Réponses à la question ' + (q + 1)">
                    <button v-for="choix in question.choix" :key="choix.id" type="button"
                            class="puce" :class="classeChoix(question, choix)"
                            role="radio" :aria-checked="reponses[question.id] === choix.id ? 'true' : 'false'"
                            :disabled="!!resultat || envoi"
                            @click="reponses[question.id] = choix.id">
                        {{ choix.libelle }}
                    </button>
                </div>

                <template v-if="resultat">
                    <p class="quiz__verdict">
                        {{ resultat.corrections[question.id].juste ? 'Bonne réponse !' : 'Pas tout à fait.' }}
                    </p>
                    <p v-if="resultat.corrections[question.id].explication" class="chapo quiz__explication">
                        <svg class="icone" width="16" height="16" fill="none" aria-hidden="true" focusable="false"><use href="#i-ampoule"></use></svg>
                        {{ resultat.corrections[question.id].explication }}
                    </p>
                </template>
            </fieldset>

            <div v-if="!resultat" class="quiz__actions">
                <button type="button" class="bouton bouton--primaire bouton--large"
                        :disabled="!toutRepondu || envoi" @click="valider">
                    {{ envoi ? 'Correction…' : 'Valider mes réponses' }}
                </button>
                <p v-if="!toutRepondu" class="champ__aide">Réponds à toutes les questions pour valider.</p>
            </div>

            <div v-if="erreur" class="message message--erreur quiz__erreur" role="alert"><div>{{ erreur }}</div></div>
        </div>
    `,

    props: {
        questions: { type: Array, required: true },
        urlCorrection: { type: String, required: true },
        jetonCsrf: { type: String, required: true },
        seuil: { type: Number, required: true },
        urlCours: { type: String, required: true },
        // Mode examen final : tentatives limitées, bonnes réponses révélées
        // seulement après réussite ou épuisement des tentatives.
        examen: { type: Boolean, default: false },
        tentativesRestantes: { type: Number, default: null },
    },

    setup(props) {
        const racine = ref(null);
        const blocResultat = ref(null);
        // Identifiant de question → identifiant du choix retenu.
        const reponses = ref({});
        const resultat = ref(null);
        const envoi = ref(false);
        const erreur = ref(null);

        const nbRepondues = computed(() => Object.keys(reponses.value).length);
        const toutRepondu = computed(() => nbRepondues.value === props.questions.length);
        const epuise = computed(() => props.examen && resultat.value && !resultat.value.reussi
            && resultat.value.tentativesRestantes === 0);
        const bilanTexte = computed(() => {
            const { score, total } = resultat.value;
            return `${score} bonne${score > 1 ? 's' : ''} réponse${score > 1 ? 's' : ''} sur ${total}`;
        });

        const classeChoix = (question, choix) => {
            const choisi = reponses.value[question.id] === choix.id;
            if (!resultat.value) {
                return { 'est-choisie': choisi };
            }
            const correction = resultat.value.corrections[question.id];
            // Examen raté avec des tentatives restantes : le serveur ne dit que
            // si la réponse est juste, sans révéler la bonne.
            if (correction.choixCorrects.length === 0) {
                return {
                    'est-active': choisi,
                    'puce--correct': choisi && correction.juste,
                    'puce--incorrect': choisi && !correction.juste,
                };
            }
            const correct = correction.choixCorrects.includes(choix.id);
            return {
                'est-active': choisi,
                'puce--correct': correct,
                'puce--incorrect': choisi && !correct,
            };
        };

        const valider = async () => {
            envoi.value = true;
            erreur.value = null;
            try {
                const reponse = await fetch(props.urlCorrection, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': props.jetonCsrf },
                    body: JSON.stringify({ reponses: reponses.value }),
                });
                const donnees = await reponse.json();
                if (!reponse.ok) {
                    erreur.value = donnees.erreur || 'La correction a échoué. Réessaie.';
                    return;
                }
                resultat.value = donnees;
                await nextTick();
                blocResultat.value?.focus();
                blocResultat.value?.scrollIntoView({ block: 'center' });
            } catch (e) {
                erreur.value = 'Connexion impossible. Vérifie ta connexion internet et réessaie.';
            } finally {
                envoi.value = false;
            }
        };

        const recommencer = () => {
            reponses.value = {};
            resultat.value = null;
            racine.value?.focus();
            racine.value?.scrollIntoView();
        };

        return {
            racine, blocResultat, reponses, resultat, envoi, erreur,
            nbRepondues, toutRepondu, epuise, bilanTexte, classeChoix, valider, recommencer,
        };
    },
};
