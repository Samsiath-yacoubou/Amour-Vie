<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260823055522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rebrand Amour&Vie ABMS : ajoute Centre Jeune, Emission, Temoignage, '
            .'QuestionAnonyme, NewsletterAbonne, Quiz/QuizQuestion/QuizChoix ; '
            .'supprime le sous-système Alerte précoce ; ajoute Parametre.instagram/tiktok.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE centre_jeune (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(180) NOT NULL, slug VARCHAR(200) NOT NULL, ville VARCHAR(150) NOT NULL, adresse LONGTEXT NOT NULL, description LONGTEXT DEFAULT NULL, telephone VARCHAR(50) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, horaires VARCHAR(255) DEFAULT NULL, responsable VARCHAR(150) DEFAULT NULL, latitude NUMERIC(10, 7) DEFAULT NULL, longitude NUMERIC(10, 7) DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, ordre INT NOT NULL, actif TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, programme_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_E63EBAE2989D9B62 (slug), INDEX IDX_E63EBAE262BB7AEE (programme_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE centre_jeune_thematique (centre_jeune_id INT NOT NULL, thematique_id INT NOT NULL, INDEX IDX_1C0413E0136C9A81 (centre_jeune_id), INDEX IDX_1C0413E0476556AF (thematique_id), PRIMARY KEY (centre_jeune_id, thematique_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE emission (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, slug VARCHAR(280) NOT NULL, type VARCHAR(20) NOT NULL, description LONGTEXT DEFAULT NULL, url_externe VARCHAR(500) DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, date_diffusion DATE DEFAULT NULL, animateurs VARCHAR(255) DEFAULT NULL, publie TINYINT NOT NULL, ordre INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, programme_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_F0225CF4989D9B62 (slug), INDEX IDX_F0225CF462BB7AEE (programme_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE emission_thematique (emission_id INT NOT NULL, thematique_id INT NOT NULL, INDEX IDX_909BE99717E24D70 (emission_id), INDEX IDX_909BE997476556AF (thematique_id), PRIMARY KEY (emission_id, thematique_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE newsletter_abonne (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, prenom VARCHAR(100) DEFAULT NULL, actif TINYINT NOT NULL, adresse_ip VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_CC68D0B9E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE question_anonyme (id INT AUTO_INCREMENT NOT NULL, pseudonyme VARCHAR(100) DEFAULT NULL, question LONGTEXT NOT NULL, reponse LONGTEXT DEFAULT NULL, repondant_libre VARCHAR(150) DEFAULT NULL, statut VARCHAR(20) NOT NULL, ordre INT NOT NULL, adresse_ip VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL, repondue_at DATETIME DEFAULT NULL, repondant_id INT DEFAULT NULL, INDEX IDX_FABF9056C5DBCCD6 (repondant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE question_anonyme_thematique (question_anonyme_id INT NOT NULL, thematique_id INT NOT NULL, INDEX IDX_F113BD9024EFB3CF (question_anonyme_id), INDEX IDX_F113BD90476556AF (thematique_id), PRIMARY KEY (question_anonyme_id, thematique_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(200) NOT NULL, slug VARCHAR(220) NOT NULL, description LONGTEXT DEFAULT NULL, actif TINYINT NOT NULL, ordre INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, programme_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_A412FA92989D9B62 (slug), INDEX IDX_A412FA9262BB7AEE (programme_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quiz_thematique (quiz_id INT NOT NULL, thematique_id INT NOT NULL, INDEX IDX_73E1F294853CD175 (quiz_id), INDEX IDX_73E1F294476556AF (thematique_id), PRIMARY KEY (quiz_id, thematique_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quiz_choix (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, est_correct TINYINT NOT NULL, ordre INT NOT NULL, question_id INT NOT NULL, INDEX IDX_B33BF9111E27F6BF (question_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quiz_question (id INT AUTO_INCREMENT NOT NULL, enonce LONGTEXT NOT NULL, explication LONGTEXT DEFAULT NULL, ordre INT NOT NULL, quiz_id INT NOT NULL, INDEX IDX_6033B00B853CD175 (quiz_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE temoignage (id INT AUTO_INCREMENT NOT NULL, pseudonyme VARCHAR(100) DEFAULT NULL, age INT DEFAULT NULL, ville VARCHAR(100) DEFAULT NULL, contenu LONGTEXT NOT NULL, photo_name VARCHAR(255) DEFAULT NULL, statut VARCHAR(20) NOT NULL, note_interne LONGTEXT DEFAULT NULL, adresse_ip VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL, programme_id INT DEFAULT NULL, INDEX IDX_BDADBC4662BB7AEE (programme_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE centre_jeune ADD CONSTRAINT FK_E63EBAE262BB7AEE FOREIGN KEY (programme_id) REFERENCES programme (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE centre_jeune_thematique ADD CONSTRAINT FK_1C0413E0136C9A81 FOREIGN KEY (centre_jeune_id) REFERENCES centre_jeune (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE centre_jeune_thematique ADD CONSTRAINT FK_1C0413E0476556AF FOREIGN KEY (thematique_id) REFERENCES thematique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE emission ADD CONSTRAINT FK_F0225CF462BB7AEE FOREIGN KEY (programme_id) REFERENCES programme (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE emission_thematique ADD CONSTRAINT FK_909BE99717E24D70 FOREIGN KEY (emission_id) REFERENCES emission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE emission_thematique ADD CONSTRAINT FK_909BE997476556AF FOREIGN KEY (thematique_id) REFERENCES thematique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE question_anonyme ADD CONSTRAINT FK_FABF9056C5DBCCD6 FOREIGN KEY (repondant_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE question_anonyme_thematique ADD CONSTRAINT FK_F113BD9024EFB3CF FOREIGN KEY (question_anonyme_id) REFERENCES question_anonyme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE question_anonyme_thematique ADD CONSTRAINT FK_F113BD90476556AF FOREIGN KEY (thematique_id) REFERENCES thematique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA9262BB7AEE FOREIGN KEY (programme_id) REFERENCES programme (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE quiz_thematique ADD CONSTRAINT FK_73E1F294853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_thematique ADD CONSTRAINT FK_73E1F294476556AF FOREIGN KEY (thematique_id) REFERENCES thematique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_choix ADD CONSTRAINT FK_B33BF9111E27F6BF FOREIGN KEY (question_id) REFERENCES quiz_question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE quiz_question ADD CONSTRAINT FK_6033B00B853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE temoignage ADD CONSTRAINT FK_BDADBC4662BB7AEE FOREIGN KEY (programme_id) REFERENCES programme (id) ON DELETE SET NULL');
        $this->addSql('DROP TABLE alerte');
        $this->addSql('ALTER TABLE parametre ADD instagram VARCHAR(255) DEFAULT NULL, ADD tiktok VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alerte (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, slug VARCHAR(280) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, zone VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, categorie VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, niveau VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, date_alerte DATE NOT NULL, description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, source VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, latitude NUMERIC(10, 7) DEFAULT NULL, longitude NUMERIC(10, 7) DEFAULT NULL, publie TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_3AE753A989D9B62 (slug), INDEX idx_alerte_date (date_alerte), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE centre_jeune DROP FOREIGN KEY FK_E63EBAE262BB7AEE');
        $this->addSql('ALTER TABLE centre_jeune_thematique DROP FOREIGN KEY FK_1C0413E0136C9A81');
        $this->addSql('ALTER TABLE centre_jeune_thematique DROP FOREIGN KEY FK_1C0413E0476556AF');
        $this->addSql('ALTER TABLE emission DROP FOREIGN KEY FK_F0225CF462BB7AEE');
        $this->addSql('ALTER TABLE emission_thematique DROP FOREIGN KEY FK_909BE99717E24D70');
        $this->addSql('ALTER TABLE emission_thematique DROP FOREIGN KEY FK_909BE997476556AF');
        $this->addSql('ALTER TABLE question_anonyme DROP FOREIGN KEY FK_FABF9056C5DBCCD6');
        $this->addSql('ALTER TABLE question_anonyme_thematique DROP FOREIGN KEY FK_F113BD9024EFB3CF');
        $this->addSql('ALTER TABLE question_anonyme_thematique DROP FOREIGN KEY FK_F113BD90476556AF');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA9262BB7AEE');
        $this->addSql('ALTER TABLE quiz_thematique DROP FOREIGN KEY FK_73E1F294853CD175');
        $this->addSql('ALTER TABLE quiz_thematique DROP FOREIGN KEY FK_73E1F294476556AF');
        $this->addSql('ALTER TABLE quiz_choix DROP FOREIGN KEY FK_B33BF9111E27F6BF');
        $this->addSql('ALTER TABLE quiz_question DROP FOREIGN KEY FK_6033B00B853CD175');
        $this->addSql('ALTER TABLE temoignage DROP FOREIGN KEY FK_BDADBC4662BB7AEE');
        $this->addSql('DROP TABLE centre_jeune');
        $this->addSql('DROP TABLE centre_jeune_thematique');
        $this->addSql('DROP TABLE emission');
        $this->addSql('DROP TABLE emission_thematique');
        $this->addSql('DROP TABLE newsletter_abonne');
        $this->addSql('DROP TABLE question_anonyme');
        $this->addSql('DROP TABLE question_anonyme_thematique');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE quiz_thematique');
        $this->addSql('DROP TABLE quiz_choix');
        $this->addSql('DROP TABLE quiz_question');
        $this->addSql('DROP TABLE temoignage');
        $this->addSql('ALTER TABLE parametre DROP instagram, DROP tiktok');
    }
}
