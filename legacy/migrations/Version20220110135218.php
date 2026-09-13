<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220110135218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gallery (id UUID NOT NULL, cover_id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_472B783A922726E9 ON gallery (cover_id)');
        $this->addSql('COMMENT ON COLUMN gallery.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN gallery.cover_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE gallery_media (gallery_id UUID NOT NULL, media_id UUID NOT NULL, PRIMARY KEY(gallery_id, media_id))');
        $this->addSql('CREATE INDEX IDX_8EB1712F4E7AF8F ON gallery_media (gallery_id)');
        $this->addSql('CREATE INDEX IDX_8EB1712FEA9FDD75 ON gallery_media (media_id)');
        $this->addSql('COMMENT ON COLUMN gallery_media.gallery_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN gallery_media.media_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE media (id UUID NOT NULL, name VARCHAR(255) NOT NULL, size INT NOT NULL, alt VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN media.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE gallery ADD CONSTRAINT FK_472B783A922726E9 FOREIGN KEY (cover_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE gallery_media ADD CONSTRAINT FK_8EB1712F4E7AF8F FOREIGN KEY (gallery_id) REFERENCES gallery (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE gallery_media ADD CONSTRAINT FK_8EB1712FEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX idx_4ce6c7a4dd795681 RENAME TO IDX_4CE6C7A4BDBA6A61');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gallery_media DROP CONSTRAINT FK_8EB1712F4E7AF8F');
        $this->addSql('ALTER TABLE gallery DROP CONSTRAINT FK_472B783A922726E9');
        $this->addSql('ALTER TABLE gallery_media DROP CONSTRAINT FK_8EB1712FEA9FDD75');
        $this->addSql('DROP TABLE gallery');
        $this->addSql('DROP TABLE gallery_media');
        $this->addSql('DROP TABLE media');
        $this->addSql('ALTER INDEX idx_4ce6c7a4bdba6a61 RENAME TO idx_4ce6c7a4dd795681');
    }
}
