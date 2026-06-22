<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260622120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename article type "auto" to "watch" and update related page refs.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE article SET type = 'watch' WHERE type = 'auto'");
        $this->addSql("UPDATE page SET ref = 'watch' WHERE ref = 'auto'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE article SET type = 'auto' WHERE type = 'watch'");
        $this->addSql("UPDATE page SET ref = 'auto' WHERE ref = 'watch'");
    }
}
