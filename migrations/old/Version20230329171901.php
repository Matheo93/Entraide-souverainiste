<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230329171901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE announces (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, localisation VARCHAR(255) NOT NULL, distance INT NOT NULL, is_remote TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, date_added DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cities (id INT AUTO_INCREMENT NOT NULL, code_insee VARCHAR(25) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, code INT DEFAULT NULL, libelle VARCHAR(255) DEFAULT NULL, coords VARCHAR(255) DEFAULT NULL, ligne_5 VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mails (id INT AUTO_INCREMENT NOT NULL, send_at DATETIME NOT NULL, sender VARCHAR(255) NOT NULL, receiver VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pages (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, registered_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, active TINYINT(1) NOT NULL, INDEX IDX_2074E575F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE settings (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stats (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, user_id INT DEFAULT NULL, content LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', registered_at DATETIME NOT NULL, INDEX IDX_574767AAC54C8C93 (type_id), INDEX IDX_574767AAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stats_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE texts (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT DEFAULT NULL, registered_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, registered_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_addresses (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, insee VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, postal VARCHAR(255) NOT NULL, lat VARCHAR(255) NOT NULL, lng VARCHAR(255) NOT NULL, distance VARCHAR(255) NOT NULL, INDEX IDX_6F2AF8F2A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_attachments (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, hash VARCHAR(300) NOT NULL, filename LONGTEXT NOT NULL, filehash VARCHAR(300) NOT NULL, uploaded_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, mime VARCHAR(255) NOT NULL, size INT NOT NULL, slug VARCHAR(255) NOT NULL, INDEX IDX_EEDF3343A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_genders (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, callable VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_informations (id INT AUTO_INCREMENT NOT NULL, gender_id INT DEFAULT NULL, user_id INT NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, street LONGTEXT DEFAULT NULL, postal_code VARCHAR(255) DEFAULT NULL, city LONGTEXT DEFAULT NULL, INDEX IDX_EF5A188B708A0E0 (gender_id), UNIQUE INDEX UNIQ_EF5A188BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_profile_picture (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, hash VARCHAR(255) NOT NULL, uploaded_at DATETIME NOT NULL, filename VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_D7B9FD9AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pages ADD CONSTRAINT FK_2074E575F675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE stats ADD CONSTRAINT FK_574767AAC54C8C93 FOREIGN KEY (type_id) REFERENCES stats_type (id)');
        $this->addSql('ALTER TABLE stats ADD CONSTRAINT FK_574767AAA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_addresses ADD CONSTRAINT FK_6F2AF8F2A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_attachments ADD CONSTRAINT FK_EEDF3343A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_informations ADD CONSTRAINT FK_EF5A188B708A0E0 FOREIGN KEY (gender_id) REFERENCES user_genders (id)');
        $this->addSql('ALTER TABLE user_informations ADD CONSTRAINT FK_EF5A188BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_profile_picture ADD CONSTRAINT FK_D7B9FD9AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stats DROP FOREIGN KEY FK_574767AAC54C8C93');
        $this->addSql('ALTER TABLE pages DROP FOREIGN KEY FK_2074E575F675F31B');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE stats DROP FOREIGN KEY FK_574767AAA76ED395');
        $this->addSql('ALTER TABLE user_addresses DROP FOREIGN KEY FK_6F2AF8F2A76ED395');
        $this->addSql('ALTER TABLE user_attachments DROP FOREIGN KEY FK_EEDF3343A76ED395');
        $this->addSql('ALTER TABLE user_informations DROP FOREIGN KEY FK_EF5A188BA76ED395');
        $this->addSql('ALTER TABLE user_profile_picture DROP FOREIGN KEY FK_D7B9FD9AA76ED395');
        $this->addSql('ALTER TABLE user_informations DROP FOREIGN KEY FK_EF5A188B708A0E0');
        $this->addSql('DROP TABLE announces');
        $this->addSql('DROP TABLE cities');
        $this->addSql('DROP TABLE mails');
        $this->addSql('DROP TABLE pages');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE settings');
        $this->addSql('DROP TABLE stats');
        $this->addSql('DROP TABLE stats_type');
        $this->addSql('DROP TABLE texts');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE user_addresses');
        $this->addSql('DROP TABLE user_attachments');
        $this->addSql('DROP TABLE user_genders');
        $this->addSql('DROP TABLE user_informations');
        $this->addSql('DROP TABLE user_profile_picture');
    }
}
