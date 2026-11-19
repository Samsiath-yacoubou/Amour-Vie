<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260903103019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parametre ADD afficher_fil_reseaux_sociaux TINYINT NOT NULL, ADD fil_reseaux_sociaux_titre VARCHAR(150) DEFAULT NULL, ADD fil_reseaux_sociaux_code LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parametre DROP afficher_fil_reseaux_sociaux, DROP fil_reseaux_sociaux_titre, DROP fil_reseaux_sociaux_code');
    }
}
