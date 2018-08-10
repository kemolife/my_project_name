<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180809100738 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE youtube_post (id INT NOT NULL, account_id INT DEFAULT NULL, INDEX IDX_DD1874AB9B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE youtube_account (id INT NOT NULL, access_token VARCHAR(255) NOT NULL, refresh_token VARCHAR(255) DEFAULT NULL, expires_in DATETIME NOT NULL, google_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE youtube_post ADD CONSTRAINT FK_DD1874AB9B6B5FBA FOREIGN KEY (account_id) REFERENCES social_network_account (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE youtube_post ADD CONSTRAINT FK_DD1874ABBF396750 FOREIGN KEY (id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE youtube_account ADD CONSTRAINT FK_DDEC0640BF396750 FOREIGN KEY (id) REFERENCES social_network_account (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE youtube_post');
        $this->addSql('DROP TABLE youtube_account');
    }
}
