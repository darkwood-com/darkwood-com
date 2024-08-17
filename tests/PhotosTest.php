<?php

declare(strict_types=1);

namespace App\Tests;

class PhotosTest extends CommonWebTestCase
{
    public function getHostParameter(): string
    {
        return 'photos_host';
    }

    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful($url)
    {
        $this->validatePageUrl($url);
    }

    /**
     * @dataProvider urlProvider
     */
    public function testW3C($url)
    {
        $this->validateW3CUrl($url);
    }

    public static function urlProvider(): iterable
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

        $urls = [...$commonUrls, ['/'], ['/en'], ['/de'], ['/plan-du-site'], ['/en/sitemap'], ['/de/sitemap'], ['/sitemap.xml'], ['/en/sitemap.xml'], ['/de/sitemap.xml'], ['/rss'], ['/en/rss'], ['/de/rss'], ['/contact'], ['/en/contact'], ['/de/kontakt'], ['/visualisez'], ['/en/show'], ['/de/anzeigen'], ['/demonstration'], ['/en/demo'], ['/de/demonstration'], ['/aide'], ['/en/help'], ['/de/hilfe']];

        $flattenedUrls = array_map(static function ($url) {
            return $url[0];
        }, $urls);

        return array_combine($flattenedUrls, $urls);
    }
}
