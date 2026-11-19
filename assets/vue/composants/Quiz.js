import { ref, computed, nextTick } from '../vue.js';

/**
 * Îlot « quiz pédagogique » (templates/quiz/detail.html.twig).
 *
 * Tout se joue côté client : aucune tentative n'est envoyée ni enregistrée
 * (cf. QuizController::detail()). Au clic sur une réponse, on marque le choix
 * comme correct/incorrect, on révèle la bonne réponse si besoin, on affiche
 * l'explication et on verrouille la question. Le score s'affiche une fois
 * toutes les questions répondues.
 */
export default {
    template: `
        <div class="quiz" ref="racine" tabindex="-1">
            <div class="quiz__progression">
                <p class="quiz__compteur" aria-live="polite">
                    {{ nbRepondues }} / {{ questions.length }} question{{ questions.length > 1 ? 's' : '' }} répondue{{ nbRepondues > 1 ? 's' : '' }}
                </p>
                <div class="quiz__barre" role="progressbar" :aria-valuenow="nbRepondues"
                     aria-valuemin="0" :aria-valuemax="questions.length" aria-label="Progression du quiz">
                    <span :style="{ width: (nbRepondues / questions.length * 100) + '%' }"></span>
                </div>
            </div>

            <fieldset v-for="(question, q) in questions" :key="q" class="quiz__question">
                <legend>
                    <span class="sr-only">Question {{ q + 1 }} sur {{ questions.length }} : </span>
                    {{ question.enonce }}
                </legend>

                <div class="puces" role="group" aria-label="Réponses possibles">
                    <button v-for="(choix, c) in question.choix" :key="c" type="button"
                            class="puce" :class="classeChoix(q, c)"
                            :disabled="estRepondue(q)"
                            :aria-pressed="reponses[q] === c ? 'true' : 'false'"
                            :aria-describedby="question.explication ? 'quiz-explication-' + q : null"
                            @click="choisir(q, c)">
                        {{ choix.libelle }}
                    </button>
                </div>

                <p v-if="estRepondue(q)" class="quiz__verdict" role="status">
                    {{ question.choix[reponses[q]].correct ? 'Bonne réponse !' : 'Pas tout à fait.' }}
                </p>

                <p v-if="question.explication" v-show="estRepondue(q)" :id="'quiz-explication-' + q"
                   class="chapo quiz__explication">
                    <svg class="icone" width="16" height="16" fill="none" aria-hidden="true" focusable="false"><use href="#i-ampoule"></use></svg>
                    {{ question.explication }}
                </p>
            </fieldset>

            <div v-if="termine" class="quiz__resultat" role="status" ref="resultat" tabindex="-1">
                <p class="quiz__score">{{ score }} / {{ questions.length }}</p>
                <p>{{ messageResultat }}</p>
                <button type="button" class="bouton bouton--primaire" @click="recommencer">Recommencer le quiz</button>
            </div>
        </div>
    `,

    props: {
        questions: { type: Array, required: true },
    },

    setup(props) {
        const racine = ref(null);
        const resultat = ref(null);
        // Index de la question → index du choix retenu.
        const reponses = ref({});

        const estRepondue = (q) => q in reponses.value;
        const nbRepondues = computed(() => Object.keys(reponses.value).length);
        const termine = computed(() => nbRepondues.value === props.questions.length);
        const score = computed(() =>
            Object.entries(reponses.value)
                .filter(([q, c]) => props.questions[q].choix[c].correct)
                .length
        );

        const messageResultat = computed(() => {
            const ratio = score.value / props.questions.length;
            if (ratio === 1) return 'Parfait ! Tu maîtrises le sujet.';
            if (ratio >= 0.5) return 'Bien joué ! Relis les explications pour aller plus loin.';
            return 'Pas de souci : les explications sont là pour t\'aider. Tu peux recommencer quand tu veux.';
        });

        const choisir = async (q, c) => {
            if (estRepondue(q)) return;
            reponses.value = { ...reponses.value, [q]: c };

            if (termine.value) {
                await nextTick();
                resultat.value?.focus();
            }
        };

        const classeChoix = (q, c) => {
            if (!estRepondue(q)) return null;
            const choisi = reponses.value[q] === c;
            const correct = props.questions[q].choix[c].correct;
            return {
                'est-active': choisi,
                'puce--correct': correct && (choisi || !props.questions[q].choix[reponses.value[q]].correct),
                'puce--incorrect': choisi && !correct,
            };
        };

        const recommencer = () => {
            reponses.value = {};
            racine.value?.focus();
            racine.value?.scrollIntoView({
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
            });
        };

        return {
            racine, resultat, reponses, estRepondue, nbRepondues, termine, score,
            messageResultat, choisir, classeChoix, recommencer,
        };
    },
};
