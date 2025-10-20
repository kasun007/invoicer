<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251020013230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Step 1: Drop foreign key constraints if they exist
        $this->addSql('ALTER TABLE invoices DROP CONSTRAINT IF EXISTS fk_6a2f2f959395c3f3');
        $this->addSql('ALTER TABLE invoices DROP CONSTRAINT IF EXISTS FK_6A2F2F959395C3F3');

        // Step 2: Rename users table to customers (this preserves all data)
        $this->addSql('ALTER TABLE IF EXISTS users RENAME TO customers');

        // Step 3: Rename the sequence
        $this->addSql('ALTER SEQUENCE IF EXISTS users_id_seq RENAME TO customers_id_seq');

        // Step 4: Rename the unique index
        $this->addSql('ALTER INDEX IF EXISTS uniq_1483a5e9e7927c74 RENAME TO uniq_62534e21e7927c74');

        // Step 5: Add the foreign key constraint pointing to customers table
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F959395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // Reverse the migration: rename customers back to users
        $this->addSql('ALTER TABLE invoices DROP CONSTRAINT IF EXISTS FK_6A2F2F959395C3F3');
        $this->addSql('ALTER TABLE IF EXISTS customers RENAME TO users');
        $this->addSql('ALTER SEQUENCE IF EXISTS customers_id_seq RENAME TO users_id_seq');
        $this->addSql('ALTER INDEX IF EXISTS uniq_62534e21e7927c74 RENAME TO uniq_1483a5e9e7927c74');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT fk_6a2f2f959395c3f3 FOREIGN KEY (customer_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
