<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180716070210 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE business_info ADD category_id INT DEFAULT NULL, ADD email VARCHAR(255) NOT NULL, DROP category, CHANGE address address VARCHAR(255) NOT NULL, CHANGE phone_number phone_number VARCHAR(100) NOT NULL, CHANGE opening_hours opening_hours LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE business_info ADD CONSTRAINT FK_9B12335A12469DE2 FOREIGN KEY (category_id) REFERENCES additional_categories_business_info (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9B12335A12469DE2 ON business_info (category_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE business_info DROP FOREIGN KEY FK_9B12335A12469DE2');
        $this->addSql('DROP INDEX UNIQ_9B12335A12469DE2 ON business_info');
        $this->addSql('ALTER TABLE business_info ADD category VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, DROP category_id, DROP email, CHANGE address address VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE phone_number phone_number VARCHAR(100) DEFAULT NULL COLLATE utf8_unicode_ci, CHANGE opening_hours opening_hours TIME DEFAULT NULL');
    }
}
