<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260519100306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, utilisateur VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('DROP TABLE campus');
        $this->addSql('DROP TABLE lieu');
        $this->addSql('DROP TABLE ville');
        $this->addSql('DROP INDEX inscriptions_participants_fk ON inscription');
        $this->addSql('ALTER TABLE inscription ADD id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('DROP INDEX sorties_lieux_fk ON sortie');
        $this->addSql('DROP INDEX sorties_participants_fk ON sortie');
        $this->addSql('DROP INDEX campus_sortie_fk ON sortie');
        $this->addSql('ALTER TABLE sortie MODIFY id_sortie INT NOT NULL');
        $this->addSql('ALTER TABLE sortie ADD id INT AUTO_INCREMENT NOT NULL, ADD date_debut DATETIME NOT NULL, ADD date_cloture DATETIME NOT NULL, ADD nb_inscriptions_max INT NOT NULL, ADD url_photo VARCHAR(255) DEFAULT NULL, DROP datedebut, DROP datecloture, DROP nbInscriptionsMax, DROP urlPhoto, DROP id_campus, CHANGE id_sortie id_sortie INT NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE nom no VARCHAR(30) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('DROP INDEX pseudo ON utilisateur');
        $this->addSql('DROP INDEX participants_campus_fk ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur MODIFY id_utilisateur INT NOT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD email VARCHAR(180) NOT NULL, ADD roles JSON NOT NULL, ADD is_verified TINYINT NOT NULL, DROP pseudo, DROP nom, DROP prenom, DROP telephone, DROP mail, DROP administrateur, DROP actif, DROP id_campus, CHANGE id_utilisateur id INT AUTO_INCREMENT NOT NULL, CHANGE motDePasse password VARCHAR(255) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON utilisateur (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE campus (id_campus INT AUTO_INCREMENT NOT NULL, nom_campus VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, id_ville INT NOT NULL, INDEX campus_ville_fk (id_ville), PRIMARY KEY (id_campus)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE lieu (id_lieu INT AUTO_INCREMENT NOT NULL, nom_lieu VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, rue VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, latitude FLOAT DEFAULT NULL, longitude FLOAT DEFAULT NULL, id_ville INT NOT NULL, INDEX lieux_villes_fk (id_ville), PRIMARY KEY (id_lieu)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ville (id_ville INT AUTO_INCREMENT NOT NULL, nom_ville VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, code_postal VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, PRIMARY KEY (id_ville)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('ALTER TABLE inscription MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE inscription DROP id, DROP PRIMARY KEY, ADD PRIMARY KEY (id_sortie, id_participant)');
        $this->addSql('CREATE INDEX inscriptions_participants_fk ON inscription (id_participant)');
        $this->addSql('ALTER TABLE sortie MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE sortie ADD datedebut DATETIME NOT NULL, ADD datecloture DATETIME NOT NULL, ADD urlPhoto VARCHAR(250) DEFAULT NULL, ADD id_campus INT NOT NULL, DROP id, DROP date_debut, DROP date_cloture, DROP url_photo, CHANGE id_sortie id_sortie INT AUTO_INCREMENT NOT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE no nom VARCHAR(30) NOT NULL, CHANGE nb_inscriptions_max nbInscriptionsMax INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_sortie)');
        $this->addSql('CREATE INDEX sorties_lieux_fk ON sortie (id_lieu)');
        $this->addSql('CREATE INDEX sorties_participants_fk ON sortie (id_organisateur)');
        $this->addSql('CREATE INDEX campus_sortie_fk ON sortie (id_campus)');
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_EMAIL ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD pseudo VARCHAR(30) NOT NULL, ADD nom VARCHAR(30) NOT NULL, ADD prenom VARCHAR(30) NOT NULL, ADD telephone VARCHAR(15) DEFAULT NULL, ADD mail VARCHAR(20) NOT NULL, ADD actif TINYINT NOT NULL, ADD id_campus INT NOT NULL, DROP email, DROP roles, CHANGE id id_utilisateur INT AUTO_INCREMENT NOT NULL, CHANGE password motDePasse VARCHAR(255) NOT NULL, CHANGE is_verified administrateur TINYINT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_utilisateur)');
        $this->addSql('CREATE UNIQUE INDEX pseudo ON utilisateur (pseudo)');
        $this->addSql('CREATE INDEX participants_campus_fk ON utilisateur (id_campus)');
    }
}
