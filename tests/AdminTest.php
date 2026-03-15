<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\Attributes\DataProvider;

class AdminTest extends CommonWebTestCase
{
    public function getHostParameter(): string
    {
        return 'admin_host';
    }

    //#[DataProvider('urls')]
    /*public function testPageIsSuccessful($url)
    {
        $this->validatePageUrl($url);
    }*/

    #[DataProvider('urls')]
    public function testW3C($url)
    {
        $this->validateW3CUrl($url);
    }

    /**
     * @dataProvider provideUrlsCases
     */
    /*public function testPageIsAuth($url, $location)
    {
        $client = $this->getHostClient();
        $client->request('GET', $url);

        self::assertTrue($client->getResponse()->isRedirect($location));
    }*/

    public static function urls(): iterable
    {
        return [
            ['/login'],
        ];
    }

    public static function provideUrlsCases(): iterable
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
