<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180807145357 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE business_info CHANGE region_code region_code VARCHAR(255) DEFAULT NULL, CHANGE administrative_area administrative_area VARCHAR(255) DEFAULT NULL, CHANGE locality locality VARCHAR(255) DEFAULT NULL, CHANGE latitude latitude DOUBLE PRECISION DEFAULT NULL, CHANGE longitude longitude DOUBLE PRECISION DEFAULT NULL, CHANGE postal_code postal_code VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE business_info CHANGE region_code region_code VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE administrative_area administrative_area VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE locality locality VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE latitude latitude DOUBLE PRECISION NOT NULL, CHANGE longitude longitude DOUBLE PRECISION NOT NULL, CHANGE postal_code postal_code VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
