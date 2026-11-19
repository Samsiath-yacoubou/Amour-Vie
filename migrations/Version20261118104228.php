<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261118104228 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'E-learning (phases 2 à 4) : examen final, certificats, signature des certificats, rappels.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE certificat (id INT AUTO_INCREMENT NOT NULL, numero VARCHAR(20) NOT NULL, nom_titulaire VARCHAR(210) NOT NULL, titre_cours VARCHAR(200) NOT NULL, score_examen INT DEFAULT NULL, delivre_le DATETIME NOT NULL, reemis_le DATETIME DEFAULT NULL, revoque_le DATETIME DEFAULT NULL, motif_revocation VARCHAR(255) DEFAULT NULL, apprenant_id INT DEFAULT NULL, cours_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_27448F77F55AE19E (numero), UNIQUE INDEX uniq_certificat_apprenant_cours (apprenant_id, cours_id), INDEX IDX_27448F77C5697D6D (apprenant_id), INDEX IDX_27448F777ECF78B0 (cours_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tentative_examen (id INT AUTO_INCREMENT NOT NULL, score INT NOT NULL, total INT NOT NULL, reussie TINYINT NOT NULL, created_at DATETIME NOT NULL, apprenant_id INT NOT NULL, cours_id INT NOT NULL, INDEX idx_tentative_examen_apprenant_cours (apprenant_id, cours_id), INDEX IDX_55010E25C5697D6D (apprenant_id), INDEX IDX_55010E257ECF78B0 (cours_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE certificat ADD CONSTRAINT FK_27448F77C5697D6D FOREIGN KEY (apprenant_id) REFERENCES apprenant (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE certificat ADD CONSTRAINT FK_27448F777ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tentative_examen ADD CONSTRAINT FK_55010E25C5697D6D FOREIGN KEY (apprenant_id) REFERENCES apprenant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tentative_examen ADD CONSTRAINT FK_55010E257ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cours ADD tentatives_examen_max INT DEFAULT 3 NOT NULL, ADD examen_final_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9C2F747F9B FOREIGN KEY (examen_final_id) REFERENCES quiz (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_FDCA8C9C2F747F9B ON cours (examen_final_id)');
        $this->addSql('ALTER TABLE inscription_cours ADD rappel_envoye_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE parametre ADD certificat_signataire_nom VARCHAR(150) DEFAULT NULL, ADD certificat_signataire_fonction VARCHAR(150) DEFAULT NULL, ADD certificat_signature_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificat DROP FOREIGN KEY FK_27448F77C5697D6D');
        $this->addSql('ALTER TABLE certificat DROP FOREIGN KEY FK_27448F777ECF78B0');
        $this->addSql('ALTER TABLE tentative_examen DROP FOREIGN KEY FK_55010E25C5697D6D');
        $this->addSql('ALTER TABLE tentative_examen DROP FOREIGN KEY FK_55010E257ECF78B0');
        $this->addSql('DROP TABLE certificat');
        $this->addSql('DROP TABLE tentative_examen');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9C2F747F9B');
        $this->addSql('DROP INDEX IDX_FDCA8C9C2F747F9B ON cours');
        $this->addSql('ALTER TABLE cours DROP tentatives_examen_max, DROP examen_final_id');
        $this->addSql('ALTER TABLE inscription_cours DROP rappel_envoye_at');
        $this->addSql('ALTER TABLE parametre DROP certificat_signataire_nom, DROP certificat_signataire_fonction, DROP certificat_signature_name');
    }
}
