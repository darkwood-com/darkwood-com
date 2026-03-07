<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260307110636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add darkwood_archive table for premium archives snapshot persistence.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE darkwood_archive (id INT AUTO_INCREMENT NOT NULL, archive_date DATE NOT NULL, payload JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_darkwood_archive_date (archive_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE darkwood_archive');
    }
}
