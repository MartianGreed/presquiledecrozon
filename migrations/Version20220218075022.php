<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220218075022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TYPE "booking_status" AS ENUM(\'initialised\', \'booked\', \'confirmed\', \'done\')');
        $this->addSql('CREATE TABLE booking (id UUID NOT NULL, rental_id UUID NOT NULL, booker_id UUID NOT NULL, start_at DATE NOT NULL, end_at DATE NOT NULL, people_count INT NOT NULL, status booking_status  NOT NULL, prices JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E00CEDDEA7CF2329 ON booking (rental_id)');
        $this->addSql('CREATE INDEX IDX_E00CEDDE8B7E4006 ON booking (booker_id)');
        $this->addSql('COMMENT ON COLUMN booking.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN booking.rental_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN booking.booker_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN booking.status IS \'(DC2Type:booking_status)\'');
        $this->addSql('COMMENT ON COLUMN booking.prices IS \'(DC2Type:booking_prices)\'');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA7CF2329 FOREIGN KEY (rental_id) REFERENCES rental (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE8B7E4006 FOREIGN KEY (booker_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('COMMENT ON COLUMN bed.size IS \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TYPE booking_status');
        $this->addSql('COMMENT ON COLUMN bed.size IS NULL');
    }
}
