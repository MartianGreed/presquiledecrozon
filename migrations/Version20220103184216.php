<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220103184216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bedroom_bed (bedroom_id UUID NOT NULL, bed_id UUID NOT NULL, PRIMARY KEY(bedroom_id, bed_id))');
        $this->addSql('CREATE INDEX IDX_D49D1F02BDB6797C ON bedroom_bed (bedroom_id)');
        $this->addSql('CREATE INDEX IDX_D49D1F0288688BB9 ON bedroom_bed (bed_id)');
        $this->addSql('COMMENT ON COLUMN bedroom_bed.bedroom_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bedroom_bed.bed_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE bedroom_bed ADD CONSTRAINT FK_D49D1F02BDB6797C FOREIGN KEY (bedroom_id) REFERENCES bedroom (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bedroom_bed ADD CONSTRAINT FK_D49D1F0288688BB9 FOREIGN KEY (bed_id) REFERENCES bed (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bedroom DROP CONSTRAINT fk_e61543515544de86');
        $this->addSql('DROP INDEX idx_e61543515544de86');
        $this->addSql('ALTER TABLE bedroom DROP beds_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE bedroom_bed');
        $this->addSql('ALTER TABLE bedroom ADD beds_id UUID NOT NULL');
        $this->addSql('COMMENT ON COLUMN bedroom.beds_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE bedroom ADD CONSTRAINT fk_e61543515544de86 FOREIGN KEY (beds_id) REFERENCES bed (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_e61543515544de86 ON bedroom (beds_id)');
    }
}
