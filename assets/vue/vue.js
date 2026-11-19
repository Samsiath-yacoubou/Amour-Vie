/**
 * Point d'entrée unique de Vue.js pour les îlots du site public.
 *
 * On sert le build « navigateur » complet (compilateur de templates inclus) :
 * les composants déclarent leur gabarit en chaîne `template`, sans étape de
 * compilation Node. Le build runtime seul n'afficherait rien.
 */
export * from 'vue/dist/vue.esm-browser.prod.js';
