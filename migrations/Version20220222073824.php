<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220222073824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation ADD booking_id UUID');
        $this->addSql('COMMENT ON COLUMN conversation.booking_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E93301C60 FOREIGN KEY (booking_id) REFERENCES booking (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8A8E26E93301C60 ON conversation (booking_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation DROP CONSTRAINT FK_8A8E26E93301C60');
        $this->addSql('DROP INDEX IDX_8A8E26E93301C60');
        $this->addSql('ALTER TABLE conversation DROP booking_id');
    }
}
