<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180811094126 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE linkedin_post (id INT NOT NULL, account_id INT DEFAULT NULL, post_id VARCHAR(255) DEFAULT NULL, INDEX IDX_AA6BD73A9B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE linkedin_post ADD CONSTRAINT FK_AA6BD73A9B6B5FBA FOREIGN KEY (account_id) REFERENCES social_network_account (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE linkedin_post ADD CONSTRAINT FK_AA6BD73ABF396750 FOREIGN KEY (id) REFERENCES post (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE linkedin_post');
    }
}
