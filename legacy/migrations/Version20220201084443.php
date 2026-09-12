<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220201084443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN bed.size IS \'(DC2Type:json)\'');
        $this->addSql('COMMENT ON COLUMN discount.amount IS \'(DC2Type:price)\'');
        $this->addSql('ALTER TABLE rental_subscription DROP CONSTRAINT FK_47D157EE9A1887DC');
        $this->addSql('ALTER TABLE rental_subscription DROP CONSTRAINT     rental_subscription_pkey');
        $this->addSql('ALTER TABLE rental_subscription ADD id UUID NOT NULL');
        $this->addSql('ALTER TABLE rental_subscription ADD discount_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE rental_subscription ADD amount INT NOT NULL');
        $this->addSql('CREATE TYPE subscription_status AS ENUM(\'draft\', \'active\', \'expired\')');
        $this->addSql('ALTER TABLE rental_subscription ADD status subscription_status NOT NULL');
        $this->addSql('ALTER TABLE rental_subscription ADD provider_charge_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE rental_subscription ADD paid_at DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE rental_subscription ADD expires_at DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE rental_subscription ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE rental_subscription ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('COMMENT ON COLUMN rental_subscription.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN rental_subscription.discount_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN rental_subscription.amount IS \'(DC2Type:price)\'');
        $this->addSql('COMMENT ON COLUMN rental_subscription.status IS \'(DC2Type:subscription_status)\'');
        $this->addSql('ALTER TABLE rental_subscription ADD CONSTRAINT FK_47D157EE4C7C611F FOREIGN KEY (discount_id) REFERENCES discount (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rental_subscription ADD CONSTRAINT FK_47D157EE9A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_47D157EE4C7C611F ON rental_subscription (discount_id)');
        $this->addSql('ALTER TABLE rental_subscription ADD PRIMARY KEY (id)');
        $this->addSql('COMMENT ON COLUMN subscription.amount IS \'(DC2Type:price)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN bed.size IS NULL');
        $this->addSql('COMMENT ON COLUMN discount.amount IS NULL');
        $this->addSql('ALTER TABLE rental_subscription DROP CONSTRAINT FK_47D157EE4C7C611F');
        $this->addSql('ALTER TABLE rental_subscription DROP CONSTRAINT fk_47d157ee9a1887dc');
        $this->addSql('DROP INDEX IDX_47D157EE4C7C611F');
        $this->addSql('DROP INDEX rental_subscription_pkey');
        $this->addSql('ALTER TABLE rental_subscription DROP id');
        $this->addSql('ALTER TABLE rental_subscription DROP discount_id');
        $this->addSql('ALTER TABLE rental_subscription DROP amount');
        $this->addSql('ALTER TABLE rental_subscription DROP status');
        $this->addSql('ALTER TABLE rental_subscription DROP provider_charge_id');
        $this->addSql('ALTER TABLE rental_subscription DROP paid_at');
        $this->addSql('ALTER TABLE rental_subscription DROP expires_at');
        $this->addSql('ALTER TABLE rental_subscription DROP created_at');
        $this->addSql('ALTER TABLE rental_subscription DROP updated_at');
        $this->addSql('ALTER TABLE rental_subscription ADD CONSTRAINT fk_47d157ee9a1887dc FOREIGN KEY (subscription_id) REFERENCES subscription (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rental_subscription ADD PRIMARY KEY (rental_id, subscription_id)');
        $this->addSql('DROP TYPE subscription_status');
        $this->addSql('COMMENT ON COLUMN subscription.amount IS NULL');
    }
}
