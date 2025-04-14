<?php

declare(strict_types=1);

namespace App\Tests;

class AdminTest extends CommonWebTestCase
{
    public function getHostParameter(): string
    {
        return 'admin_host';
    }

    /**
     * @dataProvider urlProvider
     */
    /*public function testPageIsSuccessful($url)
    {
        $this->validatePageUrl($url);
    }*/

    /**
     * @dataProvider provideW3CCases
     */
    public function testW3C($url)
    {
        $this->validateW3CUrl($url);
    }

    /**
     * @dataProvider provideProvideW3CCasesCases
     */
    /*public function testPageIsAuth($url, $location)
    {
        $client = $this->getHostClient();
        $client->request('GET', $url);

        self::assertTrue($client->getResponse()->isRedirect($location));
    }*/

    public static function provideW3CCases(): iterable
    {
        $commonUrls = [
            ['/login'],
        ];

        return array_merge($commonUrls, []);
    }

    public static function provideProvideW3CCasesCases(): iterable
    {
        $commonUrls = [
        ];

        $urls = array_merge($commonUrls, [
            ['/fr', '/login'],
            ['/en', '/en/login'],
            ['/de', '/de/login'],
        ]);

        $flattenedUrls = array_map(static function ($url) {
            return $url[0];
        }, $urls);

        return array_combine($flattenedUrls, $urls);
    }
}
