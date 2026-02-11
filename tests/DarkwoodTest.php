<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\Attributes\DataProvider;

use function sprintf;

class DarkwoodTest extends CommonWebTestCase
{
    public function getHostParameter(): string
    {
        return 'darkwood_host';
    }

    /**
     * @dataProvider urlProvider
     */
    /*public function testPageIsSuccessful($url)
    {
        $this->validatePageUrl($url);
    }*/

    public function testLlmsTxtExistsAndContainsExpectedContent(): void
    {
        $path = dirname(__DIR__) . '/public/llms.txt';

        self::assertFileExists($path, 'public/llms.txt should exist');
        self::assertStringContainsString('LLM / Agent Access', (string) file_get_contents($path), 'llms.txt should include "LLM / Agent Access"');
    }

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

        $mobilePlayUrls = [];
        $diplays = ['iphone', 'ipad'];
        $mobileUrls = ['login', 'chat', 'guestbook', 'rank'];
        $localeUrls = ['/jouer', '/en/play', '/de/spiel'];
        foreach ($diplays as $diplay) {
            foreach ($mobileUrls as $mobileUrl) {
                foreach ($localeUrls as $localeUrl) {
                    $mobilePlayUrls[] = [sprintf('%s/%s?state=%s', $localeUrl, $diplay, $mobileUrl)];
                }
            }
        }

        return array_merge($commonUrls, $mobilePlayUrls, [
            ['/'],
            ['/en'],
            ['/de'],
            ['/plan-du-site'],
            ['/en/sitemap'],
            ['/de/sitemap'],
            ['/sitemap.xml'],
            ['/en/sitemap.xml'],
            ['/de/sitemap.xml'],
            ['/rss'],
            ['/en/rss'],
            ['/de/rss'],
            ['/contact'],
            ['/en/contact'],
            ['/de/kontakt'],
            // array('/news/{slug}'),
            // array('/en/news/{slug}'),
            // array('/de/news/{slug}'),
            // array('/jouer/{display}'),
            // array('/en/play/{display}'),
            // array('/de/spiel/{display}'),
            ['/chat'],
            ['/en/chat'],
            ['/de/chat'],
            ['/liste-des-joueurs'],
            ['/en/player-list'],
            ['/de/liste-der-spieler'],
            ['/regles-du-jeu'],
            ['/en/rules-of-the-game'],
            ['/de/regeln-des-spiels'],
            ['/livre-d-or'],
            ['/en/guestbook'],
            ['/de/gastebuch'],
            ['/extra'],
            ['/en/extra'],
            ['/de/extra'],
            ['/classement'],
            ['/en/rank'],
            ['/de/rang'],
        ]);
    }
}
