<?php

namespace App\Tests;

class AdminTest extends CommonWebTestCase
{
    public function getHostParameter()
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
     * @dataProvider urlProviderAuth
     */
    public function testPageIsAuth($url, $location)
    {
        $client = $this->getHostClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isRedirect($location));
    }

    public function urlProvider()
    {
        $commonUrls = array(
            array('/login'),
        );

        return array_merge($commonUrls, array());
    }

    public function urlProviderAuth()
    {
        $commonUrls = array(
        );

        return array_merge($commonUrls, array(
            array('/fr', '/login'),
            array('/en', '/en/login'),
            array('/de', '/de/login'),
        ));
    }

}
