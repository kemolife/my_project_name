<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180717152556 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE google_photo ADD user_id INT DEFAULT NULL, ADD is_used SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE google_photo ADD CONSTRAINT FK_8D5A8D17A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_8D5A8D17A76ED395 ON google_photo (user_id)');
        $this->addSql('ALTER TABLE instagram_photo ADD user_id INT DEFAULT NULL, ADD is_used SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE instagram_photo ADD CONSTRAINT FK_21F2D886A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_21F2D886A76ED395 ON instagram_photo (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE google_photo DROP FOREIGN KEY FK_8D5A8D17A76ED395');
        $this->addSql('DROP INDEX IDX_8D5A8D17A76ED395 ON google_photo');
        $this->addSql('ALTER TABLE google_photo DROP user_id, DROP is_used');
        $this->addSql('ALTER TABLE instagram_photo DROP FOREIGN KEY FK_21F2D886A76ED395');
        $this->addSql('DROP INDEX IDX_21F2D886A76ED395 ON instagram_photo');
        $this->addSql('ALTER TABLE instagram_photo DROP user_id, DROP is_used');
    }
}
