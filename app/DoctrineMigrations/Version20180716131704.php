<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180716131704 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE business_info ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE business_info ADD CONSTRAINT FK_9B12335A12469DE2 FOREIGN KEY (category_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9B12335A12469DE2 ON business_info (category_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE business_info DROP FOREIGN KEY FK_9B12335A12469DE2');
        $this->addSql('DROP INDEX IDX_9B12335A12469DE2 ON business_info');
        $this->addSql('ALTER TABLE business_info DROP category_id');
    }
}
