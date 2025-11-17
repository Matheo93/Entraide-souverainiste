<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251117041157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conversation_messages (id INT AUTO_INCREMENT NOT NULL, conversation_id INT NOT NULL, sender_user_id INT NOT NULL, message LONGTEXT NOT NULL, sent_at DATETIME NOT NULL, is_read TINYINT(1) NOT NULL, INDEX IDX_3B4CA1869AC0396 (conversation_id), INDEX IDX_3B4CA1862A98155E (sender_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversations (id INT AUTO_INCREMENT NOT NULL, announce_id INT NOT NULL, user_offrant_id INT NOT NULL, user_demandeur_id INT NOT NULL, closed_by_user_id INT DEFAULT NULL, status VARCHAR(50) NOT NULL, messages_count INT NOT NULL, created_at DATETIME NOT NULL, last_message_at DATETIME DEFAULT NULL, closed_at DATETIME DEFAULT NULL, closure_type VARCHAR(50) DEFAULT NULL, points_distributed TINYINT(1) NOT NULL, INDEX IDX_C2521BF16F5DA3DE (announce_id), INDEX IDX_C2521BF1BEFC5F4D (user_offrant_id), INDEX IDX_C2521BF1156A925B (user_demandeur_id), INDEX IDX_C2521BF11B93E802 (closed_by_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ip_bans (id INT AUTO_INCREMENT NOT NULL, banned_user_id INT DEFAULT NULL, banned_by_admin_id INT DEFAULT NULL, ip_address VARCHAR(45) NOT NULL, reason VARCHAR(255) DEFAULT NULL, banned_at DATETIME NOT NULL, expires_at DATETIME DEFAULT NULL, is_active TINYINT(1) NOT NULL, related_announce_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_D86B7BA322FFD58C (ip_address), INDEX IDX_D86B7BA32CE9C1AD (banned_user_id), INDEX IDX_D86B7BA3E11410ED (banned_by_admin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE points_transactions (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, related_conversation_id INT DEFAULT NULL, points_change INT NOT NULL, balance_after INT NOT NULL, transaction_type VARCHAR(100) NOT NULL, details LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_1D4BBBA8A76ED395 (user_id), INDEX IDX_1D4BBBA884C456BF (related_conversation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_behavior_stats (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, total_offres INT NOT NULL, total_demandes INT NOT NULL, ratio_offres_demandes NUMERIC(4, 2) NOT NULL, discussions_total INT NOT NULL, discussions_avec_accord INT NOT NULL, discussions_abandonnees INT NOT NULL, taux_abandon NUMERIC(4, 2) NOT NULL, messages_total INT NOT NULL, messages_moyen_par_discussion NUMERIC(4, 2) NOT NULL, temps_reponse_moyen_heures NUMERIC(6, 2) NOT NULL, profiteur_score NUMERIC(5, 2) NOT NULL, profiteur_level VARCHAR(50) NOT NULL, points_offres INT NOT NULL, points_demandes INT NOT NULL, last_calculated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_6E434CF9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_limitations (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, limitation_type VARCHAR(50) NOT NULL, limitation_details LONGTEXT DEFAULT NULL, is_active TINYINT(1) NOT NULL, applied_at DATETIME NOT NULL, expires_at DATETIME DEFAULT NULL, reason VARCHAR(50) NOT NULL, INDEX IDX_3722B2ACA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conversation_messages ADD CONSTRAINT FK_3B4CA1869AC0396 FOREIGN KEY (conversation_id) REFERENCES conversations (id)');
        $this->addSql('ALTER TABLE conversation_messages ADD CONSTRAINT FK_3B4CA1862A98155E FOREIGN KEY (sender_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF16F5DA3DE FOREIGN KEY (announce_id) REFERENCES announces (id)');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF1BEFC5F4D FOREIGN KEY (user_offrant_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF1156A925B FOREIGN KEY (user_demandeur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF11B93E802 FOREIGN KEY (closed_by_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ip_bans ADD CONSTRAINT FK_D86B7BA32CE9C1AD FOREIGN KEY (banned_user_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ip_bans ADD CONSTRAINT FK_D86B7BA3E11410ED FOREIGN KEY (banned_by_admin_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE points_transactions ADD CONSTRAINT FK_1D4BBBA8A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE points_transactions ADD CONSTRAINT FK_1D4BBBA884C456BF FOREIGN KEY (related_conversation_id) REFERENCES conversations (id)');
        $this->addSql('ALTER TABLE user_behavior_stats ADD CONSTRAINT FK_6E434CF9A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_limitations ADD CONSTRAINT FK_3722B2ACA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE announces_metas CHANGE metas metas JSON NOT NULL');
        $this->addSql('ALTER TABLE announces_requests CHANGE data data JSON NOT NULL');
        $this->addSql('ALTER TABLE contact_form CHANGE data data JSON NOT NULL');
        $this->addSql('ALTER TABLE settings CHANGE value value JSON NOT NULL');
        $this->addSql('ALTER TABLE stats CHANGE content content JSON NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation_messages DROP FOREIGN KEY FK_3B4CA1869AC0396');
        $this->addSql('ALTER TABLE conversation_messages DROP FOREIGN KEY FK_3B4CA1862A98155E');
        $this->addSql('ALTER TABLE conversations DROP FOREIGN KEY FK_C2521BF16F5DA3DE');
        $this->addSql('ALTER TABLE conversations DROP FOREIGN KEY FK_C2521BF1BEFC5F4D');
        $this->addSql('ALTER TABLE conversations DROP FOREIGN KEY FK_C2521BF1156A925B');
        $this->addSql('ALTER TABLE conversations DROP FOREIGN KEY FK_C2521BF11B93E802');
        $this->addSql('ALTER TABLE ip_bans DROP FOREIGN KEY FK_D86B7BA32CE9C1AD');
        $this->addSql('ALTER TABLE ip_bans DROP FOREIGN KEY FK_D86B7BA3E11410ED');
        $this->addSql('ALTER TABLE points_transactions DROP FOREIGN KEY FK_1D4BBBA8A76ED395');
        $this->addSql('ALTER TABLE points_transactions DROP FOREIGN KEY FK_1D4BBBA884C456BF');
        $this->addSql('ALTER TABLE user_behavior_stats DROP FOREIGN KEY FK_6E434CF9A76ED395');
        $this->addSql('ALTER TABLE user_limitations DROP FOREIGN KEY FK_3722B2ACA76ED395');
        $this->addSql('DROP TABLE conversation_messages');
        $this->addSql('DROP TABLE conversations');
        $this->addSql('DROP TABLE ip_bans');
        $this->addSql('DROP TABLE points_transactions');
        $this->addSql('DROP TABLE user_behavior_stats');
        $this->addSql('DROP TABLE user_limitations');
        $this->addSql('ALTER TABLE contact_form CHANGE data data JSON NOT NULL');
        $this->addSql('ALTER TABLE announces_requests CHANGE data data JSON NOT NULL');
        $this->addSql('ALTER TABLE announces_metas CHANGE metas metas JSON NOT NULL');
        $this->addSql('ALTER TABLE stats CHANGE content content JSON NOT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles JSON NOT NULL');
        $this->addSql('ALTER TABLE settings CHANGE value value JSON NOT NULL');
    }
}
