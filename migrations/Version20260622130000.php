<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260622130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add blog page ref "creator" with translations (copied from release page on blog site).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            INSERT INTO page (ref, site_id, type, created, updated)
            SELECT 'creator', p.site_id, p.type, NOW(), NOW()
            FROM page p
            INNER JOIN site s ON s.id = p.site_id
            WHERE p.ref = 'release' AND s.ref = 'blog'
            LIMIT 1
            SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO page_translation (locale, page_id, title, active, created, updated)
            SELECT
                pt.locale,
                (SELECT id FROM page WHERE ref = 'creator' LIMIT 1),
                CASE pt.locale
                    WHEN 'en' THEN 'Creators'
                    WHEN 'fr' THEN 'Créateurs'
                    WHEN 'de' THEN 'Creator'
                    ELSE 'Creators'
                END,
                1,
                NOW(),
                NOW()
            FROM page_translation pt
            INNER JOIN page p ON pt.page_id = p.id AND p.ref = 'release'
            INNER JOIN site s ON s.id = p.site_id AND s.ref = 'blog'
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DELETE pt FROM page_translation pt
            INNER JOIN page p ON pt.page_id = p.id
            WHERE p.ref = 'creator'
            SQL);
        $this->addSql("DELETE FROM page WHERE ref = 'creator'");
    }
}
