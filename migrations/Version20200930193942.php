<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200930193942 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_armor CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE game_classe CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE game_enemy CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE game_gem CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE game_level_up CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE game_potion CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE game_sword CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_armor CHANGE created created DATETIME DEFAULT NULL, CHANGE updated updated DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE game_classe CHANGE created created DATETIME DEFAULT NULL, CHANGE updated updated DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE game_enemy CHANGE created created DATETIME DEFAULT NULL, CHANGE updated updated DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE game_gem CHANGE created created DATETIME DEFAULT NULL, CHANGE updated updated DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE game_level_up CHANGE created created DATETIME DEFAULT NULL, CHANGE updated updated DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE game_potion CHANGE created created DATETIME DEFAULT NULL, CHANGE updated updated DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE game_sword CHANGE created created DATETIME DEFAULT NULL, CHANGE updated updated DATETIME DEFAULT NULL');
    }
}
