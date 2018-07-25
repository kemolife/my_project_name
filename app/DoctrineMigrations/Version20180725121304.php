<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180725121304 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pinterest_pin (id INT NOT NULL, account_id INT DEFAULT NULL, media_id VARCHAR(255) DEFAULT NULL, board VARCHAR(255) NOT NULL, link VARCHAR(255) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, INDEX IDX_843A17729B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pinterest_photo (id INT NOT NULL, user_id INT DEFAULT NULL, post_id INT DEFAULT NULL, is_used SMALLINT NOT NULL, INDEX IDX_F023C51FA76ED395 (user_id), INDEX IDX_F023C51F4B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pinterest_pin ADD CONSTRAINT FK_843A17729B6B5FBA FOREIGN KEY (account_id) REFERENCES social_network_account (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE pinterest_pin ADD CONSTRAINT FK_843A1772BF396750 FOREIGN KEY (id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pinterest_photo ADD CONSTRAINT FK_F023C51FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pinterest_photo ADD CONSTRAINT FK_F023C51F4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pinterest_photo ADD CONSTRAINT FK_F023C51FBF396750 FOREIGN KEY (id) REFERENCES images (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pinterest_pin');
        $this->addSql('DROP TABLE pinterest_photo');
    }
}
