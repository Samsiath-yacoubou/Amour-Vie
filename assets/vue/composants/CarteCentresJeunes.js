import { ref, computed, onMounted } from '../vue.js';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

/**
 * Îlot « carte des Centres Jeunes Amour&Vie » (templates/centre_jeune/liste.html.twig).
 *
 * Les données sont injectées côté serveur (data-props) plutôt que rechargées
 * par une requête séparée.
 */
export default {
    template: `
        <div>
            <p class="carte-centres__etat" role="status">
                <strong>{{ centresLocalises.length }}</strong>
                centre{{ centresLocalises.length > 1 ? 's' : '' }} géolocalisé{{ centresLocalises.length > 1 ? 's' : '' }}
                sur {{ centres.length }}.
            </p>

            <div ref="conteneurCarte" class="carte-geo" role="application"
                 aria-label="Carte des Centres Jeunes Amour&Vie au Bénin"></div>

            <details class="carte-centres__repli">
                <summary>Consulter la liste des centres géolocalisés</summary>
                <ul class="carte-centres__liste">
                    <li v-for="centre in centresLocalises" :key="centre.id">
                        <span class="carte-geo__puce" style="background: var(--rose)" aria-hidden="true"></span>
                        <strong>{{ centre.nom }}</strong>,
                        {{ centre.ville }}<span v-if="centre.telephone"> · {{ centre.telephone }}</span>
                    </li>
                </ul>
            </details>
        </div>
    `,

    props: {
        centres: { type: Array, required: true },
        centreLat: { type: Number, default: 9.3077 },
        centreLon: { type: Number, default: 2.3158 },
        zoom: { type: Number, default: 7 },
    },

    setup(props) {
        const conteneurCarte = ref(null);
        const centresLocalises = computed(() =>
            props.centres.filter((c) => c.latitude && c.longitude)
        );

        /** Les libellés proviennent de la base : on les échappe avant injection HTML. */
        const echapper = (valeur) => {
            const div = document.createElement('div');
            div.textContent = valeur ?? '';
            return div.innerHTML;
        };

        onMounted(() => {
            const carte = L.map(conteneurCarte.value, {
                center: [props.centreLat, props.centreLon],
                zoom: props.zoom,
                scrollWheelZoom: false, // évite de capturer le défilement de la page
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '© Contributeurs <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            }).addTo(carte);

            const coucheMarqueurs = L.layerGroup().addTo(carte);

            centresLocalises.value.forEach((centre) => {
                L.circleMarker([centre.latitude, centre.longitude], {
                    radius: 10,
                    color: '#ffffff',
                    weight: 2,
                    fillColor: '#e3001b',
                    fillOpacity: 0.9,
                })
                    .bindPopup(`
                        <strong>${echapper(centre.nom)}</strong><br>
                        ${echapper(centre.adresse)}<br>
                        ${centre.telephone ? `<em>${echapper(centre.telephone)}</em><br>` : ''}
                        ${centre.horaires ? `<em>${echapper(centre.horaires)}</em>` : ''}
                    `)
                    .addTo(coucheMarqueurs);
            });

            // La carte est montée dans un conteneur dont la taille finale n'est
            // connue qu'après le rendu : on force le recalcul.
            setTimeout(() => carte.invalidateSize(), 100);
        });

        return { centresLocalises, conteneurCarte };
    },
};
