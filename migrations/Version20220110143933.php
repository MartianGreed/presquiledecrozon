<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220110143933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rental ADD gallery_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN rental.gallery_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27D4E7AF8F FOREIGN KEY (gallery_id) REFERENCES gallery (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1619C27D4E7AF8F ON rental (gallery_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rental DROP CONSTRAINT FK_1619C27D4E7AF8F');
        $this->addSql('DROP INDEX UNIQ_1619C27D4E7AF8F');
        $this->addSql('ALTER TABLE rental DROP gallery_id');
    }
}
