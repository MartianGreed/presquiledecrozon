<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211228101107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');
        $this->addSql('CREATE TABLE bed (id UUID NOT NULL, label VARCHAR(255) NOT NULL, help VARCHAR(255) DEFAULT NULL, size JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN bed.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE country (id UUID NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(10) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN country.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE department (id UUID NOT NULL, region_id UUID NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CD1DE18A98260155 ON department (region_id)');
        $this->addSql('COMMENT ON COLUMN department.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN department.region_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE furniture (id UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN furniture.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE linens (id UUID NOT NULL, category_id UUID NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FD4194EA12469DE2 ON linens (category_id)');
        $this->addSql('COMMENT ON COLUMN linens.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN linens.category_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE linens_category (id UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN linens_category.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE postal_code (id UUID NOT NULL, department_id UUID NOT NULL, code VARCHAR(10) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EA98E376AE80F5DF ON postal_code (department_id)');
        $this->addSql('COMMENT ON COLUMN postal_code.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN postal_code.department_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE profile (id UUID NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, preferred_language VARCHAR(10) NOT NULL, birthdate DATE DEFAULT NULL, cellphone VARCHAR(13) DEFAULT NULL, description TEXT DEFAULT NULL, gender VARCHAR(1) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN profile.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE region (id UUID NOT NULL, country_id UUID NOT NULL, name VARCHAR(255) NOT NULL, prefix1 VARCHAR(10) NOT NULL, prefix2 VARCHAR(10) NOT NULL, slug VARCHAR(255) NOT NULL, display_old_name BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F62F176F92F3E70 ON region (country_id)');
        $this->addSql('COMMENT ON COLUMN region.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN region.country_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE rental_type (id UUID NOT NULL, label VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN rental_type.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE town (id UUID NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, insee_code VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN town.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE town_postal_code (town_id UUID NOT NULL, postal_code_id UUID NOT NULL, PRIMARY KEY(town_id, postal_code_id))');
        $this->addSql('CREATE INDEX IDX_451D6A3975E23604 ON town_postal_code (town_id)');
        $this->addSql('CREATE INDEX IDX_451D6A39BDBA6A61 ON town_postal_code (postal_code_id)');
        $this->addSql('COMMENT ON COLUMN town_postal_code.town_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN town_postal_code.postal_code_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A98260155 FOREIGN KEY (region_id) REFERENCES region (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE linens ADD CONSTRAINT FK_FD4194EA12469DE2 FOREIGN KEY (category_id) REFERENCES linens_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE postal_code ADD CONSTRAINT FK_EA98E376AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE region ADD CONSTRAINT FK_F62F176F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE town_postal_code ADD CONSTRAINT FK_451D6A3975E23604 FOREIGN KEY (town_id) REFERENCES town (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE town_postal_code ADD CONSTRAINT FK_451D6A39BDBA6A61 FOREIGN KEY (postal_code_id) REFERENCES postal_code (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE region DROP CONSTRAINT FK_F62F176F92F3E70');
        $this->addSql('ALTER TABLE postal_code DROP CONSTRAINT FK_EA98E376AE80F5DF');
        $this->addSql('ALTER TABLE linens DROP CONSTRAINT FK_FD4194EA12469DE2');
        $this->addSql('ALTER TABLE town_postal_code DROP CONSTRAINT FK_451D6A39BDBA6A61');
        $this->addSql('ALTER TABLE department DROP CONSTRAINT FK_CD1DE18A98260155');
        $this->addSql('ALTER TABLE town_postal_code DROP CONSTRAINT FK_451D6A3975E23604');
        $this->addSql('DROP TABLE bed');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP TABLE furniture');
        $this->addSql('DROP TABLE linens');
        $this->addSql('DROP TABLE linens_category');
        $this->addSql('DROP TABLE postal_code');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE rental_type');
        $this->addSql('DROP TABLE town');
        $this->addSql('DROP TABLE town_postal_code');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
