<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260524120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add article_reaction table for blog emoji reactions.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE article_reaction (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, user_id INT NOT NULL, emoji VARCHAR(16) NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_8F8F09307294869C (article_id), INDEX IDX_8F8F0930A76ED395 (user_id), UNIQUE INDEX article_reaction_unique (article_id, user_id, emoji), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article_reaction ADD CONSTRAINT FK_8F8F09307294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_reaction ADD CONSTRAINT FK_8F8F0930A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article_reaction DROP FOREIGN KEY FK_8F8F09307294869C');
        $this->addSql('ALTER TABLE article_reaction DROP FOREIGN KEY FK_8F8F0930A76ED395');
        $this->addSql('DROP TABLE article_reaction');
    }
}
