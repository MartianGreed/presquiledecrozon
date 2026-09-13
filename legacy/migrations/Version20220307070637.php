<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220307070637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE favorite (id UUID NOT NULL, rental_id UUID NOT NULL, persona_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_68C58ED9A7CF2329 ON favorite (rental_id)');
        $this->addSql('CREATE INDEX IDX_68C58ED9F5F88DB9 ON favorite (persona_id)');
        $this->addSql('COMMENT ON COLUMN favorite.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN favorite.rental_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN favorite.persona_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9A7CF2329 FOREIGN KEY (rental_id) REFERENCES rental (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9F5F88DB9 FOREIGN KEY (persona_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE favorite');
    }
}
