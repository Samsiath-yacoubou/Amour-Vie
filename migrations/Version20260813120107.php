<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260813120107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alerte (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, slug VARCHAR(280) NOT NULL, zone VARCHAR(180) NOT NULL, categorie VARCHAR(150) NOT NULL, niveau VARCHAR(20) NOT NULL, date_alerte DATE NOT NULL, description LONGTEXT NOT NULL, source VARCHAR(255) DEFAULT NULL, latitude NUMERIC(10, 7) DEFAULT NULL, longitude NUMERIC(10, 7) DEFAULT NULL, publie TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_3AE753A989D9B62 (slug), INDEX idx_alerte_date (date_alerte), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, sous_titre VARCHAR(255) DEFAULT NULL, slug VARCHAR(280) NOT NULL, resume LONGTEXT NOT NULL, contenu LONGTEXT NOT NULL, image_name VARCHAR(255) DEFAULT NULL, image_alt VARCHAR(255) DEFAULT NULL, auteur_libre VARCHAR(150) DEFAULT NULL, lieu VARCHAR(150) DEFAULT NULL, statut VARCHAR(20) NOT NULL, date_publication DATETIME NOT NULL, a_la_une TINYINT NOT NULL, nombre_vues INT NOT NULL, seo_titre VARCHAR(180) DEFAULT NULL, seo_description VARCHAR(320) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, auteur_id INT DEFAULT NULL, programme_id INT DEFAULT NULL, type_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_23A0E66989D9B62 (slug), INDEX IDX_23A0E6660BB6FE6 (auteur_id), INDEX IDX_23A0E6662BB7AEE (programme_id), INDEX IDX_23A0E66C54C8C93 (type_id), INDEX idx_article_statut_date (statut, date_publication), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE article_thematique (article_id INT NOT NULL, thematique_id INT NOT NULL, INDEX IDX_65704297294869C (article_id), INDEX IDX_6570429476556AF (thematique_id), PRIMARY KEY (article_id, thematique_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE article_media (article_id INT NOT NULL, media_id INT NOT NULL, INDEX IDX_1D9BD31D7294869C (article_id), INDEX IDX_1D9BD31DEA9FDD75 (media_id), PRIMARY KEY (article_id, media_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE chiffre_cle (id INT AUTO_INCREMENT NOT NULL, valeur INT NOT NULL, suffixe VARCHAR(10) DEFAULT NULL, libelle VARCHAR(150) NOT NULL, icone VARCHAR(60) NOT NULL, ordre INT NOT NULL, actif TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, slug VARCHAR(280) NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME DEFAULT NULL, lieu VARCHAR(200) NOT NULL, description LONGTEXT NOT NULL, image_name VARCHAR(255) DEFAULT NULL, lien VARCHAR(500) DEFAULT NULL, publie TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, programme_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_B26681E989D9B62 (slug), INDEX IDX_B26681E62BB7AEE (programme_id), INDEX idx_evenement_date (date_debut), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE galerie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(200) NOT NULL, slug VARCHAR(220) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_9E7D1590989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE media (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(200) NOT NULL, type VARCHAR(30) NOT NULL, fichier_name VARCHAR(255) DEFAULT NULL, fichier_taille INT DEFAULT NULL, fichier_mime VARCHAR(120) DEFAULT NULL, fichier_original VARCHAR(255) DEFAULT NULL, alt VARCHAR(255) DEFAULT NULL, legende LONGTEXT DEFAULT NULL, url_externe VARCHAR(500) DEFAULT NULL, ordre INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, galerie_id INT DEFAULT NULL, INDEX IDX_6A2CA10C825396CB (galerie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE membre (id INT AUTO_INCREMENT NOT NULL, prenom VARCHAR(100) NOT NULL, nom VARCHAR(100) NOT NULL, slug VARCHAR(220) NOT NULL, fonction VARCHAR(180) NOT NULL, departement VARCHAR(150) DEFAULT NULL, categorie VARCHAR(40) NOT NULL, biographie LONGTEXT DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, linkedin VARCHAR(255) DEFAULT NULL, photo_name VARCHAR(255) DEFAULT NULL, ordre INT NOT NULL, actif TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_F6B4FB29989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE message_contact (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, sujet VARCHAR(200) NOT NULL, message LONGTEXT NOT NULL, lu TINYINT NOT NULL, traite TINYINT NOT NULL, note_interne LONGTEXT DEFAULT NULL, adresse_ip VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE parametre (id INT AUTO_INCREMENT NOT NULL, nom_site VARCHAR(150) NOT NULL, slogan VARCHAR(255) DEFAULT NULL, logo_name VARCHAR(255) DEFAULT NULL, logo_blanc_name VARCHAR(255) DEFAULT NULL, favicon_name VARCHAR(255) DEFAULT NULL, hero_titre VARCHAR(255) NOT NULL, hero_sous_titre LONGTEXT DEFAULT NULL, hero_image_name VARCHAR(255) DEFAULT NULL, hero_video_url VARCHAR(500) DEFAULT NULL, hero_bouton1_libelle VARCHAR(80) DEFAULT NULL, hero_bouton1_lien VARCHAR(255) DEFAULT NULL, hero_bouton2_libelle VARCHAR(80) DEFAULT NULL, hero_bouton2_lien VARCHAR(255) DEFAULT NULL, telephone VARCHAR(50) DEFAULT NULL, telephone_secondaire VARCHAR(50) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, adresse LONGTEXT DEFAULT NULL, horaires VARCHAR(255) DEFAULT NULL, maps_embed LONGTEXT DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, linkedin VARCHAR(255) DEFAULT NULL, twitter VARCHAR(255) DEFAULT NULL, youtube VARCHAR(255) DEFAULT NULL, footer_texte LONGTEXT DEFAULT NULL, copyright VARCHAR(255) DEFAULT NULL, seo_titre VARCHAR(180) DEFAULT NULL, seo_description VARCHAR(320) DEFAULT NULL, seo_mots_cles VARCHAR(500) DEFAULT NULL, image_partage_name VARCHAR(255) DEFAULT NULL, mentions_legales LONGTEXT DEFAULT NULL, politique_confidentialite LONGTEXT DEFAULT NULL, conditions_utilisation LONGTEXT DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE partenaire (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(200) NOT NULL, slug VARCHAR(220) NOT NULL, description LONGTEXT DEFAULT NULL, site_web VARCHAR(255) DEFAULT NULL, categorie VARCHAR(120) DEFAULT NULL, logo_name VARCHAR(255) DEFAULT NULL, ordre INT NOT NULL, actif TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_32FFA373989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE programme (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(180) NOT NULL, sigle VARCHAR(30) NOT NULL, slug VARCHAR(210) NOT NULL, description_courte LONGTEXT NOT NULL, description LONGTEXT DEFAULT NULL, objectifs LONGTEXT DEFAULT NULL, activites LONGTEXT DEFAULT NULL, resultats LONGTEXT DEFAULT NULL, couleur VARCHAR(20) DEFAULT NULL, logo_name VARCHAR(255) DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, ordre INT NOT NULL, actif TINYINT NOT NULL, seo_titre VARCHAR(180) DEFAULT NULL, seo_description VARCHAR(320) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_3DDCB9FF989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE programme_media (programme_id INT NOT NULL, media_id INT NOT NULL, INDEX IDX_5936FCDB62BB7AEE (programme_id), INDEX IDX_5936FCDBEA9FDD75 (media_id), PRIMARY KEY (programme_id, media_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE publication (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, slug VARCHAR(280) NOT NULL, description LONGTEXT DEFAULT NULL, annee INT NOT NULL, fichier_name VARCHAR(255) DEFAULT NULL, fichier_taille INT DEFAULT NULL, fichier_original VARCHAR(255) DEFAULT NULL, couverture_name VARCHAR(255) DEFAULT NULL, nombre_telechargements INT NOT NULL, publie TINYINT NOT NULL, a_la_une TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, type_id INT DEFAULT NULL, programme_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_AF3C6779989D9B62 (slug), INDEX IDX_AF3C6779C54C8C93 (type_id), INDEX IDX_AF3C677962BB7AEE (programme_id), INDEX idx_publication_annee (annee), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE publication_thematique (publication_id INT NOT NULL, thematique_id INT NOT NULL, INDEX IDX_54F9904938B217A7 (publication_id), INDEX IDX_54F99049476556AF (thematique_id), PRIMARY KEY (publication_id, thematique_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE section_page (id INT AUTO_INCREMENT NOT NULL, page VARCHAR(100) NOT NULL, cle VARCHAR(100) NOT NULL, titre VARCHAR(255) NOT NULL, sous_titre VARCHAR(255) DEFAULT NULL, contenu LONGTEXT DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, image_alt VARCHAR(255) DEFAULT NULL, ordre INT NOT NULL, actif TINYINT NOT NULL, updated_at DATETIME DEFAULT NULL, galerie_id INT DEFAULT NULL, INDEX IDX_91B45A2B825396CB (galerie_id), UNIQUE INDEX uniq_section_page_cle (page, cle), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE thematique (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(120) NOT NULL, slug VARCHAR(140) NOT NULL, description LONGTEXT DEFAULT NULL, couleur VARCHAR(20) DEFAULT NULL, ordre INT NOT NULL, UNIQUE INDEX UNIQ_3A8ED5A8989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE type_article (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(120) NOT NULL, slug VARCHAR(140) NOT NULL, couleur VARCHAR(20) DEFAULT NULL, ordre INT NOT NULL, UNIQUE INDEX UNIQ_2A1B6193989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE type_publication (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(120) NOT NULL, slug VARCHAR(140) NOT NULL, icone VARCHAR(60) DEFAULT NULL, couleur VARCHAR(20) DEFAULT NULL, ordre INT NOT NULL, UNIQUE INDEX UNIQ_E425B0E8989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, prenom VARCHAR(100) NOT NULL, nom VARCHAR(100) NOT NULL, fonction VARCHAR(150) DEFAULT NULL, actif TINYINT NOT NULL, derniere_connexion DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE valeur (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(150) NOT NULL, description LONGTEXT NOT NULL, icone VARCHAR(60) NOT NULL, couleur VARCHAR(20) DEFAULT NULL, ordre INT NOT NULL, actif TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6660BB6FE6 FOREIGN KEY (auteur_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6662BB7AEE FOREIGN KEY (programme_id) REFERENCES programme (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66C54C8C93 FOREIGN KEY (type_id) REFERENCES type_article (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE article_thematique ADD CONSTRAINT FK_65704297294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_thematique ADD CONSTRAINT FK_6570429476556AF FOREIGN KEY (thematique_id) REFERENCES thematique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_media ADD CONSTRAINT FK_1D9BD31D7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_media ADD CONSTRAINT FK_1D9BD31DEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E62BB7AEE FOREIGN KEY (programme_id) REFERENCES programme (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C825396CB FOREIGN KEY (galerie_id) REFERENCES galerie (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE programme_media ADD CONSTRAINT FK_5936FCDB62BB7AEE FOREIGN KEY (programme_id) REFERENCES programme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE programme_media ADD CONSTRAINT FK_5936FCDBEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE publication ADD CONSTRAINT FK_AF3C6779C54C8C93 FOREIGN KEY (type_id) REFERENCES type_publication (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE publication ADD CONSTRAINT FK_AF3C677962BB7AEE FOREIGN KEY (programme_id) REFERENCES programme (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE publication_thematique ADD CONSTRAINT FK_54F9904938B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE publication_thematique ADD CONSTRAINT FK_54F99049476556AF FOREIGN KEY (thematique_id) REFERENCES thematique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE section_page ADD CONSTRAINT FK_91B45A2B825396CB FOREIGN KEY (galerie_id) REFERENCES galerie (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6660BB6FE6');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6662BB7AEE');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66C54C8C93');
        $this->addSql('ALTER TABLE article_thematique DROP FOREIGN KEY FK_65704297294869C');
        $this->addSql('ALTER TABLE article_thematique DROP FOREIGN KEY FK_6570429476556AF');
        $this->addSql('ALTER TABLE article_media DROP FOREIGN KEY FK_1D9BD31D7294869C');
        $this->addSql('ALTER TABLE article_media DROP FOREIGN KEY FK_1D9BD31DEA9FDD75');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E62BB7AEE');
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C825396CB');
        $this->addSql('ALTER TABLE programme_media DROP FOREIGN KEY FK_5936FCDB62BB7AEE');
        $this->addSql('ALTER TABLE programme_media DROP FOREIGN KEY FK_5936FCDBEA9FDD75');
        $this->addSql('ALTER TABLE publication DROP FOREIGN KEY FK_AF3C6779C54C8C93');
        $this->addSql('ALTER TABLE publication DROP FOREIGN KEY FK_AF3C677962BB7AEE');
        $this->addSql('ALTER TABLE publication_thematique DROP FOREIGN KEY FK_54F9904938B217A7');
        $this->addSql('ALTER TABLE publication_thematique DROP FOREIGN KEY FK_54F99049476556AF');
        $this->addSql('ALTER TABLE section_page DROP FOREIGN KEY FK_91B45A2B825396CB');
        $this->addSql('DROP TABLE alerte');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_thematique');
        $this->addSql('DROP TABLE article_media');
        $this->addSql('DROP TABLE chiffre_cle');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE galerie');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE membre');
        $this->addSql('DROP TABLE message_contact');
        $this->addSql('DROP TABLE parametre');
        $this->addSql('DROP TABLE partenaire');
        $this->addSql('DROP TABLE programme');
        $this->addSql('DROP TABLE programme_media');
        $this->addSql('DROP TABLE publication');
        $this->addSql('DROP TABLE publication_thematique');
        $this->addSql('DROP TABLE section_page');
        $this->addSql('DROP TABLE thematique');
        $this->addSql('DROP TABLE type_article');
        $this->addSql('DROP TABLE type_publication');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE valeur');
    }
}
