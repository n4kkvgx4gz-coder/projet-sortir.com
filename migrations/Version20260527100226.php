<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260527100226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE campus ADD CONSTRAINT FK_9D096811A73F0036 FOREIGN KEY (ville_id) REFERENCES ville (id)');
        $this->addSql('ALTER TABLE campus RENAME INDEX fk_9d096811a73f0036 TO IDX_9D096811A73F0036');
        $this->addSql('ALTER TABLE groupe_prive ADD CONSTRAINT FK_A8D00A9D73A201E5 FOREIGN KEY (createur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE groupe_prive_utilisateur ADD CONSTRAINT FK_5313A586EFB6D465 FOREIGN KEY (groupe_prive_id) REFERENCES groupe_prive (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groupe_prive_utilisateur ADD CONSTRAINT FK_5313A586FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inscription ADD id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6CC72D953 FOREIGN KEY (sortie_id) REFERENCES sortie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D69D1C3019 FOREIGN KEY (participant_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_5E90F6D6CC72D953 ON inscription (sortie_id)');
        $this->addSql('ALTER TABLE inscription RENAME INDEX fk_5e90f6d69d1c3019 TO IDX_5E90F6D69D1C3019');
        $this->addSql('ALTER TABLE lieu ADD CONSTRAINT FK_2F577D59A73F0036 FOREIGN KEY (ville_id) REFERENCES ville (id)');
        $this->addSql('ALTER TABLE lieu RENAME INDEX fk_2f577d59a73f0036 TO IDX_2F577D59A73F0036');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reset_password_request RENAME INDEX idx_reset_user TO IDX_7CE748AA76ED395');
        $this->addSql('ALTER TABLE sortie ADD motif_annulation VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2D936B2FA FOREIGN KEY (organisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F26AB213CC FOREIGN KEY (lieu_id) REFERENCES lieu (id)');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE sortie RENAME INDEX fk_3c3fd3f2d936b2fa TO IDX_3C3FD3F2D936B2FA');
        $this->addSql('ALTER TABLE sortie RENAME INDEX fk_3c3fd3f26ab213cc TO IDX_3C3FD3F26AB213CC');
        $this->addSql('ALTER TABLE sortie RENAME INDEX fk_3c3fd3f2bcf5e72d TO IDX_3C3FD3F2BCF5E72D');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3AF5D55E1 FOREIGN KEY (campus_id) REFERENCES campus (id)');
        $this->addSql('ALTER TABLE utilisateur RENAME INDEX pseudo TO UNIQ_1D1C63B386CC499D');
        $this->addSql('ALTER TABLE utilisateur RENAME INDEX fk_1d1c63b3af5d55e1 TO IDX_1D1C63B3AF5D55E1');
        $this->addSql('ALTER TABLE utilisateur RENAME INDEX email TO UNIQ_IDENTIFIER_EMAIL');
        $this->addSql('DROP INDEX IDX_75EA56E016BA31DB ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
        $this->addSql('ALTER TABLE rememberme_token CHANGE class class VARCHAR(100) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE campus DROP FOREIGN KEY FK_9D096811A73F0036');
        $this->addSql('ALTER TABLE campus RENAME INDEX idx_9d096811a73f0036 TO FK_9D096811A73F0036');
        $this->addSql('ALTER TABLE groupe_prive DROP FOREIGN KEY FK_A8D00A9D73A201E5');
        $this->addSql('ALTER TABLE groupe_prive_utilisateur DROP FOREIGN KEY FK_5313A586EFB6D465');
        $this->addSql('ALTER TABLE groupe_prive_utilisateur DROP FOREIGN KEY FK_5313A586FB88E14F');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6CC72D953');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D69D1C3019');
        $this->addSql('DROP INDEX IDX_5E90F6D6CC72D953 ON inscription');
        $this->addSql('ALTER TABLE inscription MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE inscription DROP id, DROP PRIMARY KEY, ADD PRIMARY KEY (sortie_id, participant_id)');
        $this->addSql('ALTER TABLE inscription RENAME INDEX idx_5e90f6d69d1c3019 TO FK_5E90F6D69D1C3019');
        $this->addSql('ALTER TABLE lieu DROP FOREIGN KEY FK_2F577D59A73F0036');
        $this->addSql('ALTER TABLE lieu RENAME INDEX idx_2f577d59a73f0036 TO FK_2F577D59A73F0036');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('ALTER TABLE rememberme_token CHANGE class class VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE reset_password_request RENAME INDEX idx_7ce748aa76ed395 TO IDX_reset_user');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2D936B2FA');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F26AB213CC');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2BCF5E72D');
        $this->addSql('ALTER TABLE sortie DROP motif_annulation');
        $this->addSql('ALTER TABLE sortie RENAME INDEX idx_3c3fd3f26ab213cc TO FK_3C3FD3F26AB213CC');
        $this->addSql('ALTER TABLE sortie RENAME INDEX idx_3c3fd3f2bcf5e72d TO FK_3C3FD3F2BCF5E72D');
        $this->addSql('ALTER TABLE sortie RENAME INDEX idx_3c3fd3f2d936b2fa TO FK_3C3FD3F2D936B2FA');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3AF5D55E1');
        $this->addSql('ALTER TABLE utilisateur RENAME INDEX uniq_identifier_email TO email');
        $this->addSql('ALTER TABLE utilisateur RENAME INDEX idx_1d1c63b3af5d55e1 TO FK_1D1C63B3AF5D55E1');
        $this->addSql('ALTER TABLE utilisateur RENAME INDEX uniq_1d1c63b386cc499d TO pseudo');
    }
}
