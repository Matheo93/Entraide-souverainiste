<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230417102403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE announces_categories (announces_id INT NOT NULL, categories_id INT NOT NULL, INDEX IDX_793FC6A086751F55 (announces_id), INDEX IDX_793FC6A0A21214B7 (categories_id), PRIMARY KEY(announces_id, categories_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE announces_categories ADD CONSTRAINT FK_793FC6A086751F55 FOREIGN KEY (announces_id) REFERENCES announces (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE announces_categories ADD CONSTRAINT FK_793FC6A0A21214B7 FOREIGN KEY (categories_id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE announces ADD is_active TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE announces_categories');
        $this->addSql('ALTER TABLE announces DROP is_active');
    }
}
