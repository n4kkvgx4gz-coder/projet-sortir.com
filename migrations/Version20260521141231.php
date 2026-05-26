<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521141231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE inscription ADD id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('CREATE INDEX IDX_5E90F6D6CC72D953 ON inscription (sortie_id)');
        $this->addSql('CREATE INDEX IDX_5E90F6D69D1C3019 ON inscription (participant_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_5E90F6D6CC72D953 ON inscription');
        $this->addSql('DROP INDEX IDX_5E90F6D69D1C3019 ON inscription');

        $this->addSql('ALTER TABLE inscription MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE inscription DROP id, DROP PRIMARY KEY, ADD PRIMARY KEY (sortie_id, participant_id)');
    }
}
