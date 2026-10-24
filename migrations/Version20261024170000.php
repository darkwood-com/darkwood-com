<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Uuid;

final class Version20261024170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add immutable uuid column to user for OIDC subject identifiers.';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement(
            'ALTER TABLE user ADD uuid BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\''
        );

        $ids = $this->connection->fetchFirstColumn('SELECT id FROM user WHERE uuid IS NULL');
        foreach ($ids as $id) {
            $this->connection->executeStatement(
                'UPDATE user SET uuid = ? WHERE id = ?',
                [Uuid::v7()->toBinary(), $id],
            );
        }

        $this->connection->executeStatement(
            'ALTER TABLE user MODIFY uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\''
        );
        $this->connection->executeStatement(
            'CREATE UNIQUE INDEX UNIQ_8D93D649D17F50A6 ON user (uuid)'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_8D93D649D17F50A6 ON user');
        $this->addSql('ALTER TABLE user DROP uuid');
    }
}
