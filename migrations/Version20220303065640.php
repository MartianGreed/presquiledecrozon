<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220303065640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TYPE booking_status ADD VALUE \'cancelled\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TYPE "booking_status" RENAME TO "booking_status_old"');
        $this->addSql('CREATE TYPE "booking_status" AS ENUM(\'initialised\', \'booked\', \'confirmed\', \'done\')');
        $this->addSql('ALTER TABLE booking ALTER COLUMN "status" TYPE "booking_status" USING status::text::booking_status');
        $this->addSql('DROP TYPE "booking_status_old"');

    }
}
