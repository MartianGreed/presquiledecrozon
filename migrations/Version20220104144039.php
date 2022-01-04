<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220104144039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_6de44026989d9b62');
        $this->addSql('DROP INDEX slug_idx');
        $this->addSql('ALTER TABLE description DROP slug');
        $this->addSql('ALTER TABLE rental ADD slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1619C27D989D9B62 ON rental (slug)');
        $this->addSql('CREATE INDEX slug_idx ON rental (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE description ADD slug VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_6de44026989d9b62 ON description (slug)');
        $this->addSql('CREATE INDEX slug_idx ON description (slug)');
        $this->addSql('DROP INDEX UNIQ_1619C27D989D9B62');
        $this->addSql('DROP INDEX slug_idx');
        $this->addSql('ALTER TABLE rental DROP slug');
    }
}
