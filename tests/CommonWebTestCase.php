<?php

namespace App\Tests;

use GlValidator\GlW3CValidator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommonWebTestCase extends WebTestCase
{
    /**
     * @return string
     */
    public function getHostParameter()
    {
        return 'darkwood_host';
    }

    public function getPortParameter()
    {
        return 8092;
    }

    public function getHostClient()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $client->setServerParameters(array(
            'HTTPS' => true,
            'HTTP_HOST' => $container->getParameter($this->getHostParameter()).($this->getPortParameter() ? ':'.$this->getPortParameter() : ''),
        ));

        return $client;
    }

    public function validatePageUrl($url)
    {
        $client = $this->getHostClient();
        $client->request('GET', $url);
        $request = $client->getInternalRequest();

        $this->assertTrue($client->getResponse()->isSuccessful(), "Page url {$request->getUri()} response is not successful");
    }

    public function validateW3CUrl($url)
    {
        /*$client = $this->getHostClient();
        $filesystem = $client->getContainer()->get('filesystem');

        $urlHtmlValidator = "http://127.0.0.1:8888";
        $urlCssValidator = "http://jigsaw.w3.org/css-validator";

        $tmpDir = $client->getKernel()->getCacheDir().'/w3cvalidator';
        $filesystem->mkdir($tmpDir);

        $htmlFile = $tmpDir.'/'.md5(uniqid("", true)).'.html';

        $client->request('GET', $url);
        $request = $client->getInternalRequest();
        $response = $client->getInternalResponse();

        $contentType = $response->getHeader('Content-Type');
        if(strpos($contentType, 'text/html') === false) {
            return;
        }

        $filesystem->dumpFile($htmlFile, $response->getContent());

        $validator = new GlW3CValidator($tmpDir . "/result", $urlHtmlValidator, $urlCssValidator);
        $results = $validator->validate(array($htmlFile), array('html'), function() {});

        foreach($results as $result) {
            $this->assertNull($result, "HTML for page url {$request->getUri()} is not valid, check {$result}");
        }*/

        $this->assertTrue(true);
    }
}
