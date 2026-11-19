<?php

use App\Kernel;

// Serveur de développement PHP intégré (php -S ... public/index.php) : les
// fichiers réellement présents sur le disque (CSS/JS compilés d'EasyAdmin,
// images, favicon…) doivent être servis tels quels par PHP — avec le bon
// type MIME — plutôt que d'être exécutés comme s'ils étaient l'application.
// Sans ce garde-fou, symfony/runtime tente de "require" ces fichiers comme
// point d'entrée, ce qui casse leur contenu (voir le nonce interne
// d'autoload_runtime.php qui attend un retour "callable").
if (\PHP_SAPI === 'cli-server') {
    $cheminDemande = realpath(__DIR__.parse_url($_SERVER['REQUEST_URI'], \PHP_URL_PATH));
    if (false !== $cheminDemande && str_starts_with($cheminDemande, __DIR__.\DIRECTORY_SEPARATOR) && is_file($cheminDemande)) {
        return false;
    }
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return static function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
