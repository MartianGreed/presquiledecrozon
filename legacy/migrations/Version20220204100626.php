<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220204100626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('COMMIT;');
        $this->addSql('ALTER TYPE rental_status ADD VALUE \'expired\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TYPE "rental_status" RENAME TO "rental_status_old"');
        $this->addSql('CREATE TYPE "rental_status" AS ENUM(\'draft\', \'in-progress\', \'valid\', \'published\', \'disabled\')');
        $this->addSql('ALTER TABLE rental ALTER COLUMN "status" TYPE "rental_status" USING status::text::rental_status');
        $this->addSql('DROP TYPE "rental_status_old"');
    }
}
