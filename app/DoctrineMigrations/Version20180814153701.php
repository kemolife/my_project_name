<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180814153701 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE facebook_post ADD account_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE facebook_post ADD CONSTRAINT FK_49736F409B6B5FBA FOREIGN KEY (account_id) REFERENCES social_network_account (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_49736F409B6B5FBA ON facebook_post (account_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE facebook_post DROP FOREIGN KEY FK_49736F409B6B5FBA');
        $this->addSql('DROP INDEX IDX_49736F409B6B5FBA ON facebook_post');
        $this->addSql('ALTER TABLE facebook_post DROP account_id');
    }
}
