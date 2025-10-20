<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251019214735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE IF EXISTS invoice_items');
        $this->addSql('DROP TABLE IF EXISTS invoices');
        $this->addSql('CREATE TABLE invoice_items (id SERIAL NOT NULL, invoice_id INT NOT NULL, description VARCHAR(255) NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, line_total NUMERIC(10, 2) NOT NULL, unit VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DCC4B9F82989F1FD ON invoice_items (invoice_id)');
        $this->addSql('CREATE TABLE invoices (id SERIAL NOT NULL, customer_id INT NOT NULL, invoice_number VARCHAR(20) NOT NULL, issue_date DATE NOT NULL, due_date DATE NOT NULL, status VARCHAR(20) NOT NULL, subtotal NUMERIC(10, 2) NOT NULL, tax_amount NUMERIC(10, 2) DEFAULT NULL, tax_rate NUMERIC(5, 2) DEFAULT NULL, discount_amount NUMERIC(10, 2) DEFAULT NULL, total_amount NUMERIC(10, 2) NOT NULL, notes TEXT DEFAULT NULL, currency VARCHAR(3) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A2F2F952DA68207 ON invoices (invoice_number)');
        $this->addSql('CREATE INDEX IDX_6A2F2F959395C3F3 ON invoices (customer_id)');
        $this->addSql('COMMENT ON COLUMN invoices.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN invoices.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE invoice_items ADD CONSTRAINT FK_DCC4B9F82989F1FD FOREIGN KEY (invoice_id) REFERENCES invoices (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F959395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE invoice_items DROP CONSTRAINT FK_DCC4B9F82989F1FD');
        $this->addSql('ALTER TABLE invoices DROP CONSTRAINT FK_6A2F2F959395C3F3');
        $this->addSql('DROP TABLE IF EXISTS invoice_items');
        $this->addSql('DROP TABLE IF EXISTS invoices');
    }
}
