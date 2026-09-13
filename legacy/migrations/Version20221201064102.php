<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221201064102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bedroom_bed DROP CONSTRAINT FK_D49D1F0288688BB9');
        $this->addSql('ALTER TABLE bedroom_bed DROP CONSTRAINT bedroom_bed_pkey');
        $this->addSql('ALTER TABLE bedroom_bed ADD id UUID NOT NULL DEFAULT uuid_generate_v4()');
        $this->addSql('ALTER TABLE bedroom_bed ADD count INT DEFAULT 1 NOT NULL');
        $this->addSql('COMMENT ON COLUMN bedroom_bed.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE bedroom_bed ADD CONSTRAINT FK_D49D1F0288688BB9 FOREIGN KEY (bed_id) REFERENCES bed (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bedroom_bed ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bedroom_bed DROP CONSTRAINT fk_d49d1f0288688bb9');
        $this->addSql('DROP INDEX bedroom_bed_pkey');
        $this->addSql('ALTER TABLE bedroom_bed DROP id');
        $this->addSql('ALTER TABLE bedroom_bed DROP count');
        $this->addSql('ALTER TABLE bedroom_bed ADD CONSTRAINT fk_d49d1f0288688bb9 FOREIGN KEY (bed_id) REFERENCES bed (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bedroom_bed ADD PRIMARY KEY (bedroom_id, bed_id)');
    }
}
