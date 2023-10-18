<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200930192732 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_armor ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL');
        $this->addSql('UPDATE game_armor SET created = NOW(), updated = NOW()');
        $this->addSql('ALTER TABLE game_classe ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL');
        $this->addSql('UPDATE game_classe SET created = NOW(), updated = NOW()');
        $this->addSql('ALTER TABLE game_enemy ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL');
        $this->addSql('UPDATE game_enemy SET created = NOW(), updated = NOW()');
        $this->addSql('ALTER TABLE game_gem ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL');
        $this->addSql('UPDATE game_gem SET created = NOW(), updated = NOW()');
        $this->addSql('ALTER TABLE game_level_up ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL');
        $this->addSql('UPDATE game_level_up SET created = NOW(), updated = NOW()');
        $this->addSql('ALTER TABLE game_potion ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL');
        $this->addSql('UPDATE game_potion SET created = NOW(), updated = NOW()');
        $this->addSql('ALTER TABLE game_sword ADD created DATETIME DEFAULT NULL, ADD updated DATETIME DEFAULT NULL');
        $this->addSql('UPDATE game_sword SET created = NOW(), updated = NOW()');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_armor DROP created, DROP updated');
        $this->addSql('ALTER TABLE game_classe DROP created, DROP updated');
        $this->addSql('ALTER TABLE game_enemy DROP created, DROP updated');
        $this->addSql('ALTER TABLE game_gem DROP created, DROP updated');
        $this->addSql('ALTER TABLE game_level_up DROP created, DROP updated');
        $this->addSql('ALTER TABLE game_potion DROP created, DROP updated');
        $this->addSql('ALTER TABLE game_sword DROP created, DROP updated');
    }
}
