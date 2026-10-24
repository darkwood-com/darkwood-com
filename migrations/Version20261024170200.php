<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261024170200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop legacy custom SSO authorization code table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS sso_authorization_code');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE sso_authorization_code (code VARCHAR(64) NOT NULL, payload JSON NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, INDEX idx_sso_authorization_code_expires_at (expires_at), PRIMARY KEY (code)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }
}
