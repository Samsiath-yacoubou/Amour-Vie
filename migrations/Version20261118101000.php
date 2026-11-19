<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261118101000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'E-learning (phase 1) : apprenants, cours, modules, leçons et suivi de progression.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE apprenant (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, prenom VARCHAR(100) NOT NULL, nom VARCHAR(100) NOT NULL, telephone VARCHAR(30) DEFAULT NULL, consentement_at DATETIME NOT NULL, actif TINYINT NOT NULL, jeton_reinitialisation VARCHAR(64) DEFAULT NULL, jeton_expire_at DATETIME DEFAULT NULL, derniere_connexion DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_C4EB462EE7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE cours (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(200) NOT NULL, slug VARCHAR(220) NOT NULL, resume VARCHAR(300) NOT NULL, description LONGTEXT DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, duree_minutes INT NOT NULL, niveau VARCHAR(20) NOT NULL, seuil_reussite INT NOT NULL, publie TINYINT NOT NULL, ordre INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, programme_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_FDCA8C9C989D9B62 (slug), INDEX IDX_FDCA8C9C62BB7AEE (programme_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE inscription_cours (id INT AUTO_INCREMENT NOT NULL, terminee_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, apprenant_id INT NOT NULL, cours_id INT NOT NULL, derniere_lecon_id INT DEFAULT NULL, UNIQUE INDEX uniq_inscription_apprenant_cours (apprenant_id, cours_id), INDEX IDX_AF83D8D1C5697D6D (apprenant_id), INDEX IDX_AF83D8D17ECF78B0 (cours_id), INDEX IDX_AF83D8D1EF4BD6BA (derniere_lecon_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lecon (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(200) NOT NULL, contenu LONGTEXT NOT NULL, video_url VARCHAR(500) DEFAULT NULL, duree_minutes INT NOT NULL, ordre INT NOT NULL, module_id INT NOT NULL, INDEX IDX_94E6242EAFC2B591 (module_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lecon_terminee (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, apprenant_id INT NOT NULL, lecon_id INT NOT NULL, UNIQUE INDEX uniq_lecon_terminee (apprenant_id, lecon_id), INDEX IDX_6D83DF64C5697D6D (apprenant_id), INDEX IDX_6D83DF64EC1308A5 (lecon_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE module_cours (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(200) NOT NULL, description LONGTEXT DEFAULT NULL, ordre INT NOT NULL, cours_id INT NOT NULL, quiz_id INT DEFAULT NULL, INDEX IDX_F4C6FCF27ECF78B0 (cours_id), INDEX IDX_F4C6FCF2853CD175 (quiz_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tentative_quiz (id INT AUTO_INCREMENT NOT NULL, score INT NOT NULL, total INT NOT NULL, reussie TINYINT NOT NULL, created_at DATETIME NOT NULL, apprenant_id INT NOT NULL, module_id INT NOT NULL, INDEX idx_tentative_apprenant_module (apprenant_id, module_id), INDEX IDX_A66F2D8C5697D6D (apprenant_id), INDEX IDX_A66F2D8AFC2B591 (module_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9C62BB7AEE FOREIGN KEY (programme_id) REFERENCES programme (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE inscription_cours ADD CONSTRAINT FK_AF83D8D1C5697D6D FOREIGN KEY (apprenant_id) REFERENCES apprenant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inscription_cours ADD CONSTRAINT FK_AF83D8D17ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inscription_cours ADD CONSTRAINT FK_AF83D8D1EF4BD6BA FOREIGN KEY (derniere_lecon_id) REFERENCES lecon (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lecon ADD CONSTRAINT FK_94E6242EAFC2B591 FOREIGN KEY (module_id) REFERENCES module_cours (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lecon_terminee ADD CONSTRAINT FK_6D83DF64C5697D6D FOREIGN KEY (apprenant_id) REFERENCES apprenant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lecon_terminee ADD CONSTRAINT FK_6D83DF64EC1308A5 FOREIGN KEY (lecon_id) REFERENCES lecon (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE module_cours ADD CONSTRAINT FK_F4C6FCF27ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE module_cours ADD CONSTRAINT FK_F4C6FCF2853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tentative_quiz ADD CONSTRAINT FK_A66F2D8C5697D6D FOREIGN KEY (apprenant_id) REFERENCES apprenant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tentative_quiz ADD CONSTRAINT FK_A66F2D8AFC2B591 FOREIGN KEY (module_id) REFERENCES module_cours (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9C62BB7AEE');
        $this->addSql('ALTER TABLE inscription_cours DROP FOREIGN KEY FK_AF83D8D1C5697D6D');
        $this->addSql('ALTER TABLE inscription_cours DROP FOREIGN KEY FK_AF83D8D17ECF78B0');
        $this->addSql('ALTER TABLE inscription_cours DROP FOREIGN KEY FK_AF83D8D1EF4BD6BA');
        $this->addSql('ALTER TABLE lecon DROP FOREIGN KEY FK_94E6242EAFC2B591');
        $this->addSql('ALTER TABLE lecon_terminee DROP FOREIGN KEY FK_6D83DF64C5697D6D');
        $this->addSql('ALTER TABLE lecon_terminee DROP FOREIGN KEY FK_6D83DF64EC1308A5');
        $this->addSql('ALTER TABLE module_cours DROP FOREIGN KEY FK_F4C6FCF27ECF78B0');
        $this->addSql('ALTER TABLE module_cours DROP FOREIGN KEY FK_F4C6FCF2853CD175');
        $this->addSql('ALTER TABLE tentative_quiz DROP FOREIGN KEY FK_A66F2D8C5697D6D');
        $this->addSql('ALTER TABLE tentative_quiz DROP FOREIGN KEY FK_A66F2D8AFC2B591');
        $this->addSql('DROP TABLE apprenant');
        $this->addSql('DROP TABLE cours');
        $this->addSql('DROP TABLE inscription_cours');
        $this->addSql('DROP TABLE lecon');
        $this->addSql('DROP TABLE lecon_terminee');
        $this->addSql('DROP TABLE module_cours');
        $this->addSql('DROP TABLE tentative_quiz');
    }
}

