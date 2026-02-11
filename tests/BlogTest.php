<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Blog (blog.darkwood.com) tests.
 *
 * JSON-LD validation: run with BLOG_HOST set (e.g. in .env.test or tools/phpunit)
 * and an article present in DB; or manually view page source on an article URL
 * and check for <script type="application/ld+json">, or use
 * https://search.google.com/test/rich-results with the article URL.
 */
class BlogTest extends CommonWebTestCase
{
    public function getHostParameter(): string
    {
        return 'blog_host';
    }

    /**
     * @dataProvider urlProvider
     */
    /*public function testPageIsSuccessful($url)
    {
        $this->validatePageUrl($url);
    }*/

    #[DataProvider('provideW3CCases')]
    public function testW3C($url)
    {
        $this->validateW3CUrl($url);
    }

    public static function provideW3CCases(): iterable
    {
        $commonUrls = [
            ['/profil/matyo'],
            ['/en/profile/matyo'],
            ['/de/profil/matyo'],
            ['/login'],
            ['/inscription'],
            ['/en/register'],
            ['/de/registrieren'],
            // array('/inscription/confimer-email'),
            // array('/en/register/check-email'),
            // array('/de/registrieren/check-email'),
            // array('/inscription/confirmation/{token}'),
            // array('/en/register/confirm/{token}'),
            // array('/de/registrieren/confirm/{token}'),
            // array('/inscription/valide'),
            // array('/en/register/confirmed'),
            // array('/de/registrieren/confirmed'),
            ['/resetting/request'],
            ['/en/resetting/request'],
            ['/de/resetting/request'],
            // array('/resetting/send-email'),
            // array('/en/resetting/send-email'),
            // array('/de/resetting/send-email'),
            // array('/resetting/check-email'),
            // array('/en/resetting/check-email'),
            // array('/de/resetting/check-email'),
            // array('/resetting/reset/{token}'),
            // array('/en/resetting/reset/{token}'),
            // array('/de/resetting/reset/{token}'),
        ];

        return [...$commonUrls, ['/'], ['/en'], ['/de'], ['/plan-du-site'], ['/en/sitemap'], ['/de/sitemap'], ['/sitemap.xml'], ['/en/sitemap.xml'], ['/de/sitemap.xml'], ['/rss'], ['/en/rss'], ['/de/rss'], ['/contact'], ['/en/contact'], ['/de/kontakt'], ['/article/ecrire-ses-notes-et-les-synchroniser'], ['/en/article/ecrire-ses-notes-et-les-synchroniser'], ['/de/article/ecrire-ses-notes-et-les-synchroniser']];
    }

    public function testArticlePageHasValidJsonLdBlogPosting(): void
    {
        $client = $this->getHostClient();
        $client->request('GET', '/article/ecrire-ses-notes-et-les-synchroniser');
        if (!$client->getResponse()->isSuccessful()) {
            self::markTestSkipped('Article page not found (ensure BLOG_HOST is set and article exists in test DB)');
        }
        $content = $client->getResponse()->getContent();
        self::assertNotEmpty($content);
        self::assertStringContainsString('application/ld+json', $content);
        self::assertStringContainsString('"@type":"BlogPosting"', $content);

        if (preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $content, $m)) {
            $json = json_decode(trim($m[1]), true);
            self::assertNotNull($json, 'JSON-LD must be valid JSON');
            self::assertArrayHasKey('@context', $json);
            self::assertSame('https://schema.org', $json['@context']);
            self::assertArrayHasKey('headline', $json);
            self::assertArrayHasKey('datePublished', $json);
            self::assertArrayHasKey('author', $json);
            self::assertArrayHasKey('publisher', $json);
            self::assertArrayHasKey('mainEntityOfPage', $json);
            self::assertNotEmpty($json['headline']);
            self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T/', $json['datePublished'] ?? '');
        }
    }

    public function testBlogHomePageHasNoBlogPostingJsonLd(): void
    {
        $client = $this->getHostClient();
        $client->request('GET', '/');
        if (!$client->getResponse()->isSuccessful()) {
            self::markTestSkipped('Blog home not reachable (ensure BLOG_HOST is set in test env)');
        }
        $content = $client->getResponse()->getContent();
        self::assertNotEmpty($content);
        self::assertStringNotContainsString('"@type":"BlogPosting"', $content);
    }
}
