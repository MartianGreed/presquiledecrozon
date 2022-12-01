<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221201142442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bedroom_bed DROP CONSTRAINT FK_D49D1F02BDB6797C');
        $this->addSql('ALTER TABLE bedroom_bed ADD CONSTRAINT FK_D49D1F02BDB6797C FOREIGN KEY (bedroom_id) REFERENCES bedroom (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bedroom_bed DROP CONSTRAINT fk_d49d1f02bdb6797c');
        $this->addSql('ALTER TABLE bedroom_bed ADD CONSTRAINT fk_d49d1f02bdb6797c FOREIGN KEY (bedroom_id) REFERENCES bedroom (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
