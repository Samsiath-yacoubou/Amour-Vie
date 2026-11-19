<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    // Habillage visuel du back-office My Amour&Vie (couleurs et police du site).
    'admin-theme' => [
        'path' => './assets/styles/admin.css',
        'type' => 'css',
        'entrypoint' => true,
    ],
    // Amorce Stimulus : chargée uniquement par les pages qui en ont besoin.
    'stimulus-bootstrap' => [
        'path' => './assets/stimulus_bootstrap.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    'leaflet' => [
        'version' => '1.9.4',
    ],
    'leaflet/dist/leaflet.min.css' => [
        'version' => '1.9.4',
        'type' => 'css',
    ],
    'leaflet/dist/leaflet.css' => [
        'version' => '1.9.4',
        'type' => 'css',
    ],
    // Build navigateur complet de Vue (compilateur inclus), réexporté par assets/vue/vue.js.
    'vue/dist/vue.esm-browser.prod.js' => [
        'version' => '3.5.45',
    ],
];
