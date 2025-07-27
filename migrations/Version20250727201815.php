<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250727201815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_68c58ed9a7cf2329
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_68C58ED9A7CF2329 ON favorite (rental_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_68C58ED9A7CF2329
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_68c58ed9a7cf2329 ON favorite (rental_id)
        SQL);
    }
}
