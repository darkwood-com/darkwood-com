<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260524130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename article reaction emoji values to Jolicode-compatible short codes.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE article_reaction SET emoji = 'heart' WHERE emoji = 'love'");
        $this->addSql("UPDATE article_reaction SET emoji = 'plus1' WHERE emoji = 'thumbs_up'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE article_reaction SET emoji = 'love' WHERE emoji = 'heart'");
        $this->addSql("UPDATE article_reaction SET emoji = 'thumbs_up' WHERE emoji = 'plus1'");
    }
}
