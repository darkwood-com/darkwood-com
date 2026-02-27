<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250227100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add api_key table for Darkwood API beta access';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE api_key (
            id INT AUTO_INCREMENT NOT NULL,
            key_hash VARCHAR(64) NOT NULL,
            name VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            is_beta TINYINT(1) NOT NULL,
            is_premium TINYINT(1) NOT NULL,
            daily_action_limit INT DEFAULT NULL,
            created DATETIME NOT NULL,
            updated DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_API_KEY_HASH (key_hash),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE api_key');
    }
}
