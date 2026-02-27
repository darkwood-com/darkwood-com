<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250227000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add entitlement table for Darkwood monetization (premium subscription)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE entitlement (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            plan VARCHAR(32) NOT NULL,
            active TINYINT(1) NOT NULL,
            valid_until DATETIME DEFAULT NULL,
            created DATETIME NOT NULL,
            updated DATETIME NOT NULL,
            INDEX IDX_ENTITLEMENT_USER (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE entitlement ADD CONSTRAINT FK_ENTITLEMENT_USER FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entitlement DROP FOREIGN KEY FK_ENTITLEMENT_USER');
        $this->addSql('DROP TABLE entitlement');
    }
}
