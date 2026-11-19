<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Nature d'un fichier de la médiathèque (§ 3.11).
 */
enum TypeMedia: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case DOCUMENT = 'document';

    public function libelle(): string
    {
        return match ($this) {
            self::IMAGE => 'Image',
            self::VIDEO => 'Vidéo',
            self::AUDIO => 'Audio',
            self::DOCUMENT => 'Document',
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::IMAGE => 'fa-image',
            self::VIDEO => 'fa-video',
            self::AUDIO => 'fa-music',
            self::DOCUMENT => 'fa-file-lines',
        };
    }

    /**
     * Libellé => instance d'enum : EasyAdmin et les formulaires Symfony
     * attendent le cas lui-même, pas sa valeur scalaire.
     *
     * @return array<string, self>
     */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->libelle()] = $case;
        }

        return $choices;
    }
}
