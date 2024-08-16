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

    /**
     * @dataProvider providePageIsAuthCases
     */
    public function testPageIsAuth($url, $location)
    {
        $client = $this->getHostClient();
        $client->request('GET', $url);

        self::assertTrue($client->getResponse()->isRedirect($location));
    }

    public static function urlProvider(): iterable
    {
        $commonUrls = [
            ['/login'],
        ];

        return array_merge($commonUrls, []);
    }

    public static function providePageIsAuthCases(): iterable
    {
        $commonUrls = [
        ];

        return array_merge($commonUrls, [
            ['/fr', '/login'],
            ['/en', '/en/login'],
            ['/de', '/de/login'],
        ]);
    }
}
