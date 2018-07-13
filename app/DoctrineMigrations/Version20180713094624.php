<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180713094624 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE services_setting (id INT AUTO_INCREMENT NOT NULL, facebook_s VARCHAR(255) DEFAULT NULL, google_s VARCHAR(255) DEFAULT NULL, ratemyagent_s VARCHAR(255) DEFAULT NULL, tripadvisor_s VARCHAR(255) DEFAULT NULL, whitecoat_s VARCHAR(255) DEFAULT NULL, yahoo_s VARCHAR(255) DEFAULT NULL, yelp_s VARCHAR(255) DEFAULT NULL, zomato_s VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reviews (id INT AUTO_INCREMENT NOT NULL, site SMALLINT NOT NULL, created DATETIME NOT NULL, attribution VARCHAR(70) DEFAULT NULL, rating NUMERIC(10, 0) NOT NULL, body LONGTEXT NOT NULL, status SMALLINT DEFAULT 1, identifier VARCHAR(255) DEFAULT NULL, tag VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE social_network_account (id INT AUTO_INCREMENT NOT NULL, business_id INT DEFAULT NULL, user_id INT DEFAULT NULL, created DATETIME NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_C6A8D64DA89DB457 (business_id), INDEX IDX_C6A8D64DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, caption TEXT DEFAULT NULL, status ENUM(\'pending\', \'failed\', \'posted\') DEFAULT NULL COMMENT \'(DC2Type:enum_post_status_type)\', post_date DATETIME NOT NULL, social_network VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE google_post (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE instagram_post (id INT NOT NULL, account_id INT DEFAULT NULL, media_id VARCHAR(255) DEFAULT NULL, INDEX IDX_AA7E08A59B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bing_account (id INT NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE foursquare_account (id INT NOT NULL, user_email VARCHAR(255) DEFAULT NULL, user_password VARCHAR(255) DEFAULT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE images (id INT AUTO_INCREMENT NOT NULL, image VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE yelp_account (id INT NOT NULL, user_email VARCHAR(255) NOT NULL, user_password VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE google_account (id INT NOT NULL, access_token VARCHAR(255) NOT NULL, refresh_token VARCHAR(255) NOT NULL, expires_in DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE additional_categories_business_info (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE instagram_photo (id INT NOT NULL, post_id INT DEFAULT NULL, INDEX IDX_21F2D8864B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE instagram_account (id INT NOT NULL, name VARCHAR(255) NOT NULL, login VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, is_default SMALLINT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, full_name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) DEFAULT \'ROLE_USER\', salt VARCHAR(255) DEFAULT NULL, registration_date DATETIME DEFAULT NULL, listings SMALLINT DEFAULT NULL, reviews SMALLINT DEFAULT NULL, seo_rank SMALLINT DEFAULT NULL, messages SMALLINT DEFAULT NULL, social_posts SMALLINT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pinterest_account (id INT NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE business_info (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, category VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(100) DEFAULT NULL, website VARCHAR(100) DEFAULT NULL, description LONGTEXT DEFAULT NULL, opening_hours TIME DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, payment_options VARCHAR(255) DEFAULT NULL, video VARCHAR(255) DEFAULT NULL, INDEX IDX_9B12335AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE addition_category_business (bus_id INT NOT NULL, cat_id INT NOT NULL, INDEX IDX_85263DF2546731D (bus_id), INDEX IDX_85263DFE6ADA943 (cat_id), PRIMARY KEY(bus_id, cat_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE facebook_account (id INT NOT NULL, access_token VARCHAR(255) NOT NULL, expires_in DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE business_image (id INT NOT NULL, business_id INT DEFAULT NULL, INDEX IDX_BBDCE3E0A89DB457 (business_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE google_photo (id INT NOT NULL, post_id INT DEFAULT NULL, INDEX IDX_8D5A8D174B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE social_network_account ADD CONSTRAINT FK_C6A8D64DA89DB457 FOREIGN KEY (business_id) REFERENCES business_info (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE social_network_account ADD CONSTRAINT FK_C6A8D64DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE google_post ADD CONSTRAINT FK_BED2D98EBF396750 FOREIGN KEY (id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE instagram_post ADD CONSTRAINT FK_AA7E08A59B6B5FBA FOREIGN KEY (account_id) REFERENCES social_network_account (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE instagram_post ADD CONSTRAINT FK_AA7E08A5BF396750 FOREIGN KEY (id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bing_account ADD CONSTRAINT FK_F552C5BBF396750 FOREIGN KEY (id) REFERENCES social_network_account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE foursquare_account ADD CONSTRAINT FK_64E5AF0CBF396750 FOREIGN KEY (id) REFERENCES social_network_account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE yelp_account ADD CONSTRAINT FK_B49108EBBF396750 FOREIGN KEY (id) REFERENCES social_network_account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE google_account ADD CONSTRAINT FK_83726B22BF396750 FOREIGN KEY (id) REFERENCES social_network_account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE instagram_photo ADD CONSTRAINT FK_21F2D8864B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE instagram_photo ADD CONSTRAINT FK_21F2D886BF396750 FOREIGN KEY (id) REFERENCES images (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE instagram_account ADD CONSTRAINT FK_F029D9AABF396750 FOREIGN KEY (id) REFERENCES social_network_account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pinterest_account ADD CONSTRAINT FK_33AC55F1BF396750 FOREIGN KEY (id) REFERENCES social_network_account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE business_info ADD CONSTRAINT FK_9B12335AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE addition_category_business ADD CONSTRAINT FK_85263DF2546731D FOREIGN KEY (bus_id) REFERENCES business_info (id)');
        $this->addSql('ALTER TABLE addition_category_business ADD CONSTRAINT FK_85263DFE6ADA943 FOREIGN KEY (cat_id) REFERENCES additional_categories_business_info (id)');
        $this->addSql('ALTER TABLE facebook_account ADD CONSTRAINT FK_B0D4ADFBF396750 FOREIGN KEY (id) REFERENCES social_network_account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE business_image ADD CONSTRAINT FK_BBDCE3E0A89DB457 FOREIGN KEY (business_id) REFERENCES business_info (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE business_image ADD CONSTRAINT FK_BBDCE3E0BF396750 FOREIGN KEY (id) REFERENCES images (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE google_photo ADD CONSTRAINT FK_8D5A8D174B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE google_photo ADD CONSTRAINT FK_8D5A8D17BF396750 FOREIGN KEY (id) REFERENCES images (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE instagram_post DROP FOREIGN KEY FK_AA7E08A59B6B5FBA');
        $this->addSql('ALTER TABLE bing_account DROP FOREIGN KEY FK_F552C5BBF396750');
        $this->addSql('ALTER TABLE foursquare_account DROP FOREIGN KEY FK_64E5AF0CBF396750');
        $this->addSql('ALTER TABLE yelp_account DROP FOREIGN KEY FK_B49108EBBF396750');
        $this->addSql('ALTER TABLE google_account DROP FOREIGN KEY FK_83726B22BF396750');
        $this->addSql('ALTER TABLE instagram_account DROP FOREIGN KEY FK_F029D9AABF396750');
        $this->addSql('ALTER TABLE pinterest_account DROP FOREIGN KEY FK_33AC55F1BF396750');
        $this->addSql('ALTER TABLE facebook_account DROP FOREIGN KEY FK_B0D4ADFBF396750');
        $this->addSql('ALTER TABLE google_post DROP FOREIGN KEY FK_BED2D98EBF396750');
        $this->addSql('ALTER TABLE instagram_post DROP FOREIGN KEY FK_AA7E08A5BF396750');
        $this->addSql('ALTER TABLE instagram_photo DROP FOREIGN KEY FK_21F2D8864B89032C');
        $this->addSql('ALTER TABLE google_photo DROP FOREIGN KEY FK_8D5A8D174B89032C');
        $this->addSql('ALTER TABLE instagram_photo DROP FOREIGN KEY FK_21F2D886BF396750');
        $this->addSql('ALTER TABLE business_image DROP FOREIGN KEY FK_BBDCE3E0BF396750');
        $this->addSql('ALTER TABLE google_photo DROP FOREIGN KEY FK_8D5A8D17BF396750');
        $this->addSql('ALTER TABLE addition_category_business DROP FOREIGN KEY FK_85263DFE6ADA943');
        $this->addSql('ALTER TABLE social_network_account DROP FOREIGN KEY FK_C6A8D64DA76ED395');
        $this->addSql('ALTER TABLE business_info DROP FOREIGN KEY FK_9B12335AA76ED395');
        $this->addSql('ALTER TABLE social_network_account DROP FOREIGN KEY FK_C6A8D64DA89DB457');
        $this->addSql('ALTER TABLE addition_category_business DROP FOREIGN KEY FK_85263DF2546731D');
        $this->addSql('ALTER TABLE business_image DROP FOREIGN KEY FK_BBDCE3E0A89DB457');
        $this->addSql('DROP TABLE services_setting');
        $this->addSql('DROP TABLE reviews');
        $this->addSql('DROP TABLE social_network_account');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE google_post');
        $this->addSql('DROP TABLE instagram_post');
        $this->addSql('DROP TABLE bing_account');
        $this->addSql('DROP TABLE foursquare_account');
        $this->addSql('DROP TABLE images');
        $this->addSql('DROP TABLE yelp_account');
        $this->addSql('DROP TABLE google_account');
        $this->addSql('DROP TABLE additional_categories_business_info');
        $this->addSql('DROP TABLE instagram_photo');
        $this->addSql('DROP TABLE instagram_account');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE pinterest_account');
        $this->addSql('DROP TABLE business_info');
        $this->addSql('DROP TABLE addition_category_business');
        $this->addSql('DROP TABLE facebook_account');
        $this->addSql('DROP TABLE business_image');
        $this->addSql('DROP TABLE google_photo');
    }
}
