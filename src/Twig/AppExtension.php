<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Fonctions et filtres utilitaires partagés par les templates publics.
 */
class AppExtension extends AbstractExtension
{
    /**
     * Correspondance entre les noms d'icônes saisis dans le back-office
     * (convention Font Awesome, familière aux rédacteurs) et les symboles
     * du sprite SVG auto-hébergé.
     */
    private const ALIAS_ICONES = [
        'handshake' => 'poignee-de-main',
        'shield' => 'bouclier',
        'shield-halved' => 'bouclier',
        'scale-balanced' => 'balance',
        'balance-scale' => 'balance',
        'heart' => 'coeur',
        'globe' => 'globe',
        'lightbulb' => 'ampoule',
        'star' => 'etoile',
        'bullseye' => 'cible',
        'flag' => 'drapeau',
        'seedling' => 'pousse',
        'users' => 'utilisateurs',
        'user' => 'utilisateur',
        'user-group' => 'utilisateurs',
        'people-group' => 'utilisateurs',
        'graduation-cap' => 'formation',
        'diagram-project' => 'reseau',
        'network-wired' => 'reseau',
        'sitemap' => 'reseau',
        'file-lines' => 'document',
        'file-alt' => 'document',
        'file' => 'document',
        'book' => 'livre',
        'book-open' => 'livre',
        'newspaper' => 'journal',
        'download' => 'telecharger',
        'image' => 'image',
        'photo-film' => 'image',
        'video' => 'video',
        'music' => 'audio',
        'bullhorn' => 'megaphone',
        'quote-left' => 'citation',
        'tag' => 'etiquette',
        'folder' => 'dossier',
        'eye' => 'oeil',
        'share-nodes' => 'partager',
        'link' => 'lien',
        'phone' => 'telephone',
        'envelope' => 'enveloppe',
        'location-dot' => 'localisation',
        'map-marker-alt' => 'localisation',
        'clock' => 'horloge',
        'calendar' => 'calendrier',
        'calendar-days' => 'calendrier',
        'paper-plane' => 'envoyer',
        'comment' => 'message',
        'triangle-exclamation' => 'triangle-alerte',
        'exclamation-triangle' => 'triangle-alerte',
        'circle-info' => 'info',
        'circle-check' => 'valide',
        'bell' => 'cloche',
        'map' => 'carte',
        'map-location-dot' => 'carte',
        'arrow-trend-up' => 'tendance',
        'chart-line' => 'tendance',
        'chart-simple' => 'graphique',
        'chart-bar' => 'graphique',
        'magnifying-glass' => 'recherche',
        'search' => 'recherche',
        'sun' => 'soleil',
        'moon' => 'lune',
        'house-medical' => 'localisation',
        'podcast' => 'audio',
        'circle-question' => 'message',
        'comment-dots' => 'message',
        'file-csv' => 'document',
        'at' => 'enveloppe',
        'list-ol' => 'document',
    ];

    /** Symboles réellement présents dans templates/partials/_sprite.html.twig. */
    private const SYMBOLES = [
        'recherche', 'menu', 'fermer', 'chevron-bas', 'chevron-droite', 'deconnexion',
        'fleche-droite', 'fleche-gauche', 'fleche-haut', 'lien-externe', 'filtre', 'imprimer',
        'telephone', 'enveloppe', 'localisation', 'horloge', 'calendrier', 'envoyer', 'message',
        'document', 'livre', 'journal', 'telecharger', 'image', 'video', 'audio', 'lecture',
        'megaphone', 'citation', 'etiquette', 'dossier', 'oeil', 'partager', 'lien',
        'utilisateur', 'utilisateurs', 'poignee-de-main', 'reseau', 'formation',
        'bouclier', 'balance', 'coeur', 'globe', 'ampoule', 'etoile', 'cible', 'drapeau', 'pousse',
        'triangle-alerte', 'info', 'valide', 'cloche', 'carte', 'tendance', 'graphique',
        'facebook', 'linkedin', 'twitter', 'youtube', 'instagram', 'tiktok', 'soleil', 'lune',
    ];

    private const ICONE_DEFAUT = 'etoile';

    public function getFunctions(): array
    {
        return [
            new TwigFunction('icone', $this->icone(...), ['is_safe' => ['html']]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('extrait', $this->extrait(...)),
            new TwigFilter('date_fr', $this->dateFr(...)),
            new TwigFilter('mois_court', $this->moisCourt(...)),
            new TwigFilter('nombre_fr', $this->nombreFr(...)),
        ];
    }

    /**
     * Rend une icône du sprite SVG.
     *
     * L'icône est décorative par défaut (aria-hidden) ; passer un libellé
     * la rend accessible aux lecteurs d'écran (§ 4.4).
     */
    public function icone(string $nom, int $taille = 20, ?string $libelle = null, string $classe = ''): string
    {
        $symbole = $this->normaliserNom($nom);

        $attributs = sprintf(
            'width="%1$d" height="%1$d" class="icone%2$s" fill="none"',
            $taille,
            '' !== $classe ? ' '.htmlspecialchars($classe, \ENT_QUOTES) : ''
        );

        if (null !== $libelle) {
            return sprintf(
                '<svg %s role="img" aria-label="%s"><use href="#i-%s"></use></svg>',
                $attributs,
                htmlspecialchars($libelle, \ENT_QUOTES),
                $symbole
            );
        }

        return sprintf(
            '<svg %s aria-hidden="true" focusable="false"><use href="#i-%s"></use></svg>',
            $attributs,
            $symbole
        );
    }

    /** Résout un nom d'icône vers un symbole existant du sprite. */
    private function normaliserNom(string $nom): string
    {
        $nom = strtolower(trim($nom));
        $nom = preg_replace('/^fa[srlbd]?[\s-]+/', '', $nom) ?? $nom;
        $nom = ltrim($nom, '-');

        // Nom déjà exprimé dans la convention du sprite.
        if (\in_array($nom, self::SYMBOLES, true)) {
            return $nom;
        }

        return self::ALIAS_ICONES[$nom] ?? self::ICONE_DEFAUT;
    }

    /** Tronque un texte sans couper un mot. */
    public function extrait(?string $texte, int $longueur = 160, string $suffixe = '…'): string
    {
        $texte = trim(preg_replace('/\s+/', ' ', strip_tags((string) $texte)) ?? '');

        if (mb_strlen($texte) <= $longueur) {
            return $texte;
        }

        $coupe = mb_substr($texte, 0, $longueur);
        $dernierEspace = mb_strrpos($coupe, ' ');

        if (false !== $dernierEspace) {
            $coupe = mb_substr($coupe, 0, $dernierEspace);
        }

        return rtrim($coupe, " ,.;:").$suffixe;
    }

    /** Date au format long français, ex. « 13 août 2026 ». */
    public function dateFr(?\DateTimeInterface $date, bool $avecHeure = false): string
    {
        if (null === $date) {
            return '';
        }

        $mois = [
            1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
            'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
        ];

        $rendu = sprintf(
            '%d %s %d',
            (int) $date->format('j'),
            $mois[(int) $date->format('n')],
            (int) $date->format('Y')
        );

        return $avecHeure ? $rendu.' à '.$date->format('H\hi') : $rendu;
    }

    /** Abréviation du mois pour les pastilles de date de l'agenda. */
    public function moisCourt(?\DateTimeInterface $date): string
    {
        if (null === $date) {
            return '';
        }

        $mois = [
            1 => 'JAN', 'FÉV', 'MAR', 'AVR', 'MAI', 'JUIN',
            'JUIL', 'AOÛT', 'SEP', 'OCT', 'NOV', 'DÉC',
        ];

        return $mois[(int) $date->format('n')];
    }

    /** Nombre avec espace insécable fine comme séparateur de milliers. */
    public function nombreFr(int|float|null $nombre, int $decimales = 0): string
    {
        return number_format((float) $nombre, $decimales, ',', "\u{202F}");
    }
}
