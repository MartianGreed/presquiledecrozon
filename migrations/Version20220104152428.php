<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220104152428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE town_postal_code');
        $this->addSql('ALTER TABLE town ADD postal_code_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN town.postal_code_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE town ADD CONSTRAINT FK_4CE6C7A4DD795681 FOREIGN KEY (postal_code_id) REFERENCES postal_code (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_4CE6C7A4DD795681 ON town (postal_code_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE town_postal_code (town_id UUID NOT NULL, postal_code_id UUID NOT NULL, PRIMARY KEY(town_id, postal_code_id))');
        $this->addSql('CREATE INDEX idx_451d6a39bdba6a61 ON town_postal_code (postal_code_id)');
        $this->addSql('CREATE INDEX idx_451d6a3975e23604 ON town_postal_code (town_id)');
        $this->addSql('COMMENT ON COLUMN town_postal_code.town_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN town_postal_code.postal_code_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE town_postal_code ADD CONSTRAINT fk_451d6a3975e23604 FOREIGN KEY (town_id) REFERENCES town (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE town_postal_code ADD CONSTRAINT fk_451d6a39bdba6a61 FOREIGN KEY (postal_code_id) REFERENCES postal_code (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE town DROP CONSTRAINT FK_4CE6C7A4DD795681');
        $this->addSql('DROP INDEX IDX_4CE6C7A4DD795681');
        $this->addSql('ALTER TABLE town DROP postal_code_id');
    }
}
