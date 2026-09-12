<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211229102102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address (id UUID NOT NULL, town_id UUID NOT NULL, address VARCHAR(255) NOT NULL, address2 VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D4E6F8175E23604 ON address (town_id)');
        $this->addSql('COMMENT ON COLUMN address.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN address.town_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE bedroom (id UUID NOT NULL, beds_id UUID NOT NULL, configuration_id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E61543515544DE86 ON bedroom (beds_id)');
        $this->addSql('CREATE INDEX IDX_E615435173F32DD8 ON bedroom (configuration_id)');
        $this->addSql('COMMENT ON COLUMN bedroom.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bedroom.beds_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bedroom.configuration_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE condition (id UUID NOT NULL, animals_accepted BOOLEAN NOT NULL, smoking_allowed BOOLEAN NOT NULL, additionnal_rules TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN condition.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN condition.additionnal_rules IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE configuration (id UUID NOT NULL, type_id UUID NOT NULL, rental_id UUID NOT NULL, bathroom_count INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A5E2A5D7C54C8C93 ON configuration (type_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A5E2A5D7A7CF2329 ON configuration (rental_id)');
        $this->addSql('COMMENT ON COLUMN configuration.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN configuration.type_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN configuration.rental_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE description (id UUID NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6DE44026989D9B62 ON description (slug)');
        $this->addSql('CREATE INDEX slug_idx ON description (slug)');
        $this->addSql('COMMENT ON COLUMN description.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE geolocation (id UUID NOT NULL, coordinates JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN geolocation.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE preferences (id UUID NOT NULL, accepted_last_booking VARCHAR(255) NOT NULL, max_time_before_booking VARCHAR(255) NOT NULL, begin_booking_at TIME(0) WITHOUT TIME ZONE NOT NULL, end_booking_at TIME(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN preferences.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE price (id UUID NOT NULL, rental_id UUID NOT NULL, range_start DATE NOT NULL, range_end DATE NOT NULL, weekly_rate INT NOT NULL, daily_rate INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CAC822D9A7CF2329 ON price (rental_id)');
        $this->addSql('COMMENT ON COLUMN price.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN price.rental_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TYPE "rental_status" AS ENUM(\'draft\', \'in-progress\', \'valid\', \'published\', \'disabled\')');
        $this->addSql('CREATE TABLE rental (id UUID NOT NULL, description_id UUID DEFAULT NULL, address_id UUID DEFAULT NULL, geolocation_id UUID DEFAULT NULL, preferences_id UUID DEFAULT NULL, tax_id UUID DEFAULT NULL, condition_id UUID DEFAULT NULL, "status" rental_status NOT NULL, custom_furnitures TEXT NOT NULL, weekly_rate INT DEFAULT NULL, daily_rate INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1619C27DD9F966B ON rental (description_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1619C27DF5B7AF75 ON rental (address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1619C27D1C7B5678 ON rental (geolocation_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1619C27D7CCD6FB7 ON rental (preferences_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1619C27DB2A824D8 ON rental (tax_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1619C27D887793B6 ON rental (condition_id)');
        $this->addSql('COMMENT ON COLUMN rental.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN rental.status IS \'(DC2Type:rental_status)\'');
        $this->addSql('COMMENT ON COLUMN rental.description_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN rental.address_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN rental.geolocation_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN rental.preferences_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN rental.tax_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN rental.condition_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN rental.status IS \'(DC2Type:rental_status)\'');
        $this->addSql('COMMENT ON COLUMN rental.custom_furnitures IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE rental_furniture (rental_id UUID NOT NULL, furniture_id UUID NOT NULL, PRIMARY KEY(rental_id, furniture_id))');
        $this->addSql('CREATE INDEX IDX_D9BDDDBCA7CF2329 ON rental_furniture (rental_id)');
        $this->addSql('CREATE INDEX IDX_D9BDDDBCCF5485C3 ON rental_furniture (furniture_id)');
        $this->addSql('COMMENT ON COLUMN rental_furniture.rental_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN rental_furniture.furniture_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE tax (id UUID NOT NULL, local_tax VARCHAR(20) NOT NULL, cleaning_tax INT DEFAULT 0 NOT NULL, linens_tax INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN tax.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE unavailability (id UUID NOT NULL, rental_id UUID NOT NULL, start_at DATE NOT NULL, end_at DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F0016D1A7CF2329 ON unavailability (rental_id)');
        $this->addSql('COMMENT ON COLUMN unavailability.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN unavailability.rental_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F8175E23604 FOREIGN KEY (town_id) REFERENCES town (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bedroom ADD CONSTRAINT FK_E61543515544DE86 FOREIGN KEY (beds_id) REFERENCES bed (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bedroom ADD CONSTRAINT FK_E615435173F32DD8 FOREIGN KEY (configuration_id) REFERENCES configuration (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE configuration ADD CONSTRAINT FK_A5E2A5D7C54C8C93 FOREIGN KEY (type_id) REFERENCES rental_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE configuration ADD CONSTRAINT FK_A5E2A5D7A7CF2329 FOREIGN KEY (rental_id) REFERENCES rental (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE price ADD CONSTRAINT FK_CAC822D9A7CF2329 FOREIGN KEY (rental_id) REFERENCES rental (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27DD9F966B FOREIGN KEY (description_id) REFERENCES description (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27DF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27D1C7B5678 FOREIGN KEY (geolocation_id) REFERENCES geolocation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27D7CCD6FB7 FOREIGN KEY (preferences_id) REFERENCES preferences (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27DB2A824D8 FOREIGN KEY (tax_id) REFERENCES tax (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27D887793B6 FOREIGN KEY (condition_id) REFERENCES condition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rental_furniture ADD CONSTRAINT FK_D9BDDDBCA7CF2329 FOREIGN KEY (rental_id) REFERENCES rental (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rental_furniture ADD CONSTRAINT FK_D9BDDDBCCF5485C3 FOREIGN KEY (furniture_id) REFERENCES furniture (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE unavailability ADD CONSTRAINT FK_F0016D1A7CF2329 FOREIGN KEY (rental_id) REFERENCES rental (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bed ALTER size TYPE JSON');
        $this->addSql('ALTER TABLE bed ALTER size DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rental DROP CONSTRAINT FK_1619C27DF5B7AF75');
        $this->addSql('ALTER TABLE rental DROP CONSTRAINT FK_1619C27D887793B6');
        $this->addSql('ALTER TABLE bedroom DROP CONSTRAINT FK_E615435173F32DD8');
        $this->addSql('ALTER TABLE rental DROP CONSTRAINT FK_1619C27DD9F966B');
        $this->addSql('ALTER TABLE rental DROP CONSTRAINT FK_1619C27D1C7B5678');
        $this->addSql('ALTER TABLE rental DROP CONSTRAINT FK_1619C27D7CCD6FB7');
        $this->addSql('ALTER TABLE configuration DROP CONSTRAINT FK_A5E2A5D7A7CF2329');
        $this->addSql('ALTER TABLE price DROP CONSTRAINT FK_CAC822D9A7CF2329');
        $this->addSql('ALTER TABLE rental_furniture DROP CONSTRAINT FK_D9BDDDBCA7CF2329');
        $this->addSql('ALTER TABLE unavailability DROP CONSTRAINT FK_F0016D1A7CF2329');
        $this->addSql('ALTER TABLE rental DROP CONSTRAINT FK_1619C27DB2A824D8');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE bedroom');
        $this->addSql('DROP TABLE condition');
        $this->addSql('DROP TABLE configuration');
        $this->addSql('DROP TABLE description');
        $this->addSql('DROP TABLE geolocation');
        $this->addSql('DROP TABLE preferences');
        $this->addSql('DROP TABLE price');
        $this->addSql('DROP TABLE rental');
        $this->addSql('DROP TABLE rental_furniture');
        $this->addSql('DROP TABLE tax');
        $this->addSql('DROP TABLE unavailability');
        $this->addSql('DROP TYPE rental_status');
        $this->addSql('ALTER TABLE bed ALTER size TYPE JSON');
        $this->addSql('ALTER TABLE bed ALTER size DROP DEFAULT');
    }
}
