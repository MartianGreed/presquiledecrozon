<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220103155141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bed ALTER size TYPE JSON');
        $this->addSql('ALTER TABLE bed ALTER size DROP DEFAULT');
        $this->addSql('ALTER TABLE rental ADD owner_id UUID NOT NULL');
        $this->addSql('COMMENT ON COLUMN rental.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1619C27D7E3C61F9 ON rental (owner_id)');
        $this->addSql('ALTER TABLE "user" ADD profile_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN "user".profile_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649CCFA12B8 ON "user" (profile_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bed ALTER size TYPE JSON');
        $this->addSql('ALTER TABLE bed ALTER size DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649CCFA12B8');
        $this->addSql('DROP INDEX UNIQ_8D93D649CCFA12B8');
        $this->addSql('ALTER TABLE "user" DROP profile_id');
        $this->addSql('ALTER TABLE rental DROP CONSTRAINT FK_1619C27D7E3C61F9');
        $this->addSql('DROP INDEX IDX_1619C27D7E3C61F9');
        $this->addSql('ALTER TABLE rental DROP owner_id');
    }
}
