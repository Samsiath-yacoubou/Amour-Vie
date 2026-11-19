<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Colonnes ajoutées par les lots « audio des émissions » et « fil réseaux sociaux ».
 *
 * Cette migration était à l'origine un dump complet qui recréait des tables déjà
 * créées par Version20260823055522 (centre_jeune, emission, quiz…), ce qui faisait
 * échouer toute installation neuve sur « Table 'centre_jeune' already exists ».
 * Elle ne contient plus que les différences réelles.
 */
final class Version20261118085648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute emission.fichier_audio_name et les colonnes du fil réseaux sociaux dans parametre';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE emission ADD fichier_audio_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE parametre ADD afficher_fil_reseaux_sociaux TINYINT NOT NULL, ADD fil_reseaux_sociaux_titre VARCHAR(150) DEFAULT NULL, ADD fil_reseaux_sociaux_code LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE emission DROP fichier_audio_name');
        $this->addSql('ALTER TABLE parametre DROP afficher_fil_reseaux_sociaux, DROP fil_reseaux_sociaux_titre, DROP fil_reseaux_sociaux_code');
    }
}
