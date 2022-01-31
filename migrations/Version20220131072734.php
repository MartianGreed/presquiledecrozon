<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220131072734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE discount (id UUID NOT NULL, payee_id UUID DEFAULT NULL, type VARCHAR(10) NOT NULL, amount INT NOT NULL, code VARCHAR(255) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E1E0B40ECB4B68F ON discount (payee_id)');
        $this->addSql('COMMENT ON COLUMN discount.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN discount.payee_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE rental_subscription (rental_id UUID NOT NULL, subscription_id UUID NOT NULL, PRIMARY KEY(rental_id, subscription_id))');
        $this->addSql('CREATE INDEX IDX_47D157EEA7CF2329 ON rental_subscription (rental_id)');
        $this->addSql('CREATE INDEX IDX_47D157EE9A1887DC ON rental_subscription (subscription_id)');
        $this->addSql('COMMENT ON COLUMN rental_subscription.rental_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN rental_subscription.subscription_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE subscription (id UUID NOT NULL, amount INT NOT NULL, validity_duration VARCHAR(10) NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN subscription.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE discount ADD CONSTRAINT FK_E1E0B40ECB4B68F FOREIGN KEY (payee_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rental_subscription ADD CONSTRAINT FK_47D157EEA7CF2329 FOREIGN KEY (rental_id) REFERENCES rental (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rental_subscription ADD CONSTRAINT FK_47D157EE9A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rental_subscription DROP CONSTRAINT FK_47D157EE9A1887DC');
        $this->addSql('DROP TABLE discount');
        $this->addSql('DROP TABLE rental_subscription');
        $this->addSql('DROP TABLE subscription');
    }
}
