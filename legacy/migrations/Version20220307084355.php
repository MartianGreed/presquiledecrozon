<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220307084355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_68c58ed9a7cf2329');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_68C58ED9A7CF2329 ON favorite (rental_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_68C58ED9A7CF2329');
        $this->addSql('CREATE INDEX idx_68c58ed9a7cf2329 ON favorite (rental_id)');
    }
}
