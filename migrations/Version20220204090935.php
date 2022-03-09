<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220204090935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN price.weekly_rate IS \'(DC2Type:price)\'');
        $this->addSql('COMMENT ON COLUMN price.daily_rate IS \'(DC2Type:price)\'');
        $this->addSql('COMMENT ON COLUMN rental.weekly_rate IS \'(DC2Type:price)\'');
        $this->addSql('COMMENT ON COLUMN rental.daily_rate IS \'(DC2Type:price)\'');
        $this->addSql('ALTER TABLE rental_subscription ADD active_rental_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE rental_subscription ADD is_consumed BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('COMMENT ON COLUMN rental_subscription.active_rental_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE rental_subscription ADD CONSTRAINT FK_47D157EEB159601D FOREIGN KEY (active_rental_id) REFERENCES rental (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_47D157EEB159601D ON rental_subscription (active_rental_id)');
        $this->addSql('COMMENT ON COLUMN tax.cleaning_tax IS \'(DC2Type:price)\'');
        $this->addSql('COMMENT ON COLUMN tax.linens_tax IS \'(DC2Type:price)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN price.weekly_rate IS NULL');
        $this->addSql('COMMENT ON COLUMN price.daily_rate IS NULL');
        $this->addSql('COMMENT ON COLUMN tax.cleaning_tax IS NULL');
        $this->addSql('COMMENT ON COLUMN tax.linens_tax IS NULL');
        $this->addSql('COMMENT ON COLUMN rental.weekly_rate IS NULL');
        $this->addSql('COMMENT ON COLUMN rental.daily_rate IS NULL');
        $this->addSql('ALTER TABLE rental_subscription DROP CONSTRAINT FK_47D157EEB159601D');
        $this->addSql('DROP INDEX UNIQ_47D157EEB159601D');
        $this->addSql('ALTER TABLE rental_subscription DROP active_rental_id');
        $this->addSql('ALTER TABLE rental_subscription DROP is_consumed');
    }
}
