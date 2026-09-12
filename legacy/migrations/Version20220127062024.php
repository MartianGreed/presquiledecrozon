<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220127062024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tax_linens (tax_id UUID NOT NULL, linens_id UUID NOT NULL, PRIMARY KEY(tax_id, linens_id))');
        $this->addSql('CREATE INDEX IDX_B19BFE41B2A824D8 ON tax_linens (tax_id)');
        $this->addSql('CREATE INDEX IDX_B19BFE41D6C2AEA3 ON tax_linens (linens_id)');
        $this->addSql('COMMENT ON COLUMN tax_linens.tax_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN tax_linens.linens_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE tax_linens ADD CONSTRAINT FK_B19BFE41B2A824D8 FOREIGN KEY (tax_id) REFERENCES tax (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tax_linens ADD CONSTRAINT FK_B19BFE41D6C2AEA3 FOREIGN KEY (linens_id) REFERENCES linens (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE tax_linens');
    }
}
