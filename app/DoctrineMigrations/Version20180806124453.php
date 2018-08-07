<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180806124453 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE post ADD timezone_offset INT NOT NULL');
        $this->addSql('ALTER TABLE google_post ADD account_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE google_post ADD CONSTRAINT FK_BED2D98E9B6B5FBA FOREIGN KEY (account_id) REFERENCES social_network_account (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_BED2D98E9B6B5FBA ON google_post (account_id)');
        $this->addSql('ALTER TABLE google_account ADD google_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE google_account DROP google_id');
        $this->addSql('ALTER TABLE google_post DROP FOREIGN KEY FK_BED2D98E9B6B5FBA');
        $this->addSql('DROP INDEX IDX_BED2D98E9B6B5FBA ON google_post');
        $this->addSql('ALTER TABLE google_post DROP account_id');
        $this->addSql('ALTER TABLE post DROP timezone_offset');
    }
}
