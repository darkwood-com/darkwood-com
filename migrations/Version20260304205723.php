<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304205723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE api_key RENAME INDEX uniq_api_key_hash TO UNIQ_C912ED9D57BFB971');
        $this->addSql('DROP INDEX IDX_api_key_date ON api_key_usage');
        $this->addSql('DROP INDEX uniq_api_key_date ON api_key_usage');
        $this->addSql('ALTER TABLE api_key_usage ADD usage_count INT NOT NULL, DROP count, CHANGE date_utc usage_date DATE NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_api_key_usage_day ON api_key_usage (api_key_id, usage_date)');
        $this->addSql('ALTER TABLE entitlement RENAME INDEX idx_entitlement_user TO IDX_FA448021A76ED395');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE api_key RENAME INDEX uniq_c912ed9d57bfb971 TO UNIQ_API_KEY_HASH');
        $this->addSql('DROP INDEX uniq_api_key_usage_day ON api_key_usage');
        $this->addSql('ALTER TABLE api_key_usage ADD count INT DEFAULT 0 NOT NULL, DROP usage_count, CHANGE usage_date date_utc DATE NOT NULL');
        $this->addSql('CREATE INDEX IDX_api_key_date ON api_key_usage (api_key_id, date_utc)');
        $this->addSql('CREATE UNIQUE INDEX uniq_api_key_date ON api_key_usage (api_key_id, date_utc)');
        $this->addSql('ALTER TABLE entitlement RENAME INDEX idx_fa448021a76ed395 TO IDX_ENTITLEMENT_USER');
    }
}
