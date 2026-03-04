<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add api_key_usage table for API-key daily action counting';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE api_key_usage (
            id INT AUTO_INCREMENT NOT NULL,
            api_key_id INT NOT NULL,
            usage_date DATE NOT NULL,
            usage_count INT NOT NULL,
            INDEX IDX_API_KEY_USAGE_API_KEY (api_key_id),
            UNIQUE INDEX uniq_api_key_usage_day (api_key_id, usage_date),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE api_key_usage ADD CONSTRAINT FK_API_KEY_USAGE_API_KEY FOREIGN KEY (api_key_id) REFERENCES api_key (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE api_key_usage DROP FOREIGN KEY FK_API_KEY_USAGE_API_KEY');
        $this->addSql('DROP TABLE api_key_usage');
    }
}
