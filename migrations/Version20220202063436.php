<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220202063436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN bed.size IS \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE profile ADD picture_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN profile.picture_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0FEE45BDBF FOREIGN KEY (picture_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8157AA0FEE45BDBF ON profile (picture_id)');
        $this->addSql('ALTER TABLE "user" ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT NOW()');
        $this->addSql('ALTER TABLE "user" ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT NOW()');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN bed.size IS NULL');
        $this->addSql('ALTER TABLE profile DROP CONSTRAINT FK_8157AA0FEE45BDBF');
        $this->addSql('DROP INDEX IDX_8157AA0FEE45BDBF');
        $this->addSql('ALTER TABLE profile DROP picture_id');
        $this->addSql('ALTER TABLE "user" DROP created_at');
        $this->addSql('ALTER TABLE "user" DROP updated_at');
    }
}
