<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\ApiKey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Throwable;

class DarkwoodMonetizationTest extends CommonWebTestCase
{
    public function getHostParameter(): string
    {
        return 'api_host';
    }

    public function testFreeBetaKeyHitsDailyLimit(): void
    {
        $client = $this->createApiClientWithBetaKey(isPremium: false, dailyLimit: 2);

        $client->request('POST', '/api/darkwood/action', [], [], [], '{"query":{"state":"main"}}');
        self::assertSame(200, $client->getResponse()->getStatusCode());

        $client->request('POST', '/api/darkwood/action', [], [], [], '{"query":{"state":"main"}}');
        self::assertSame(200, $client->getResponse()->getStatusCode());

        $client->request('POST', '/api/darkwood/action', [], [], [], '{"query":{"state":"main"}}');
        self::assertSame(429, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('rate_limited', $data['error'] ?? null);
        self::assertSame('Daily action limit reached', $data['message'] ?? null);
        self::assertTrue($client->getResponse()->headers->has('Retry-After'));
    }

    public function testPremiumBetaKeyBypassesLimit(): void
    {
        $client = $this->createApiClientWithBetaKey(isPremium: true, dailyLimit: 1);

        for ($i = 0; $i < 4; $i++) {
            $client->request('POST', '/api/darkwood/action', [], [], [], '{"query":{"state":"main"}}');
            self::assertSame(200, $client->getResponse()->getStatusCode());
        }
    }

    public function testArchivesFreeKeyReturns403(): void
    {
        $client = $this->createApiClientWithBetaKey(isPremium: false, dailyLimit: 10);

        $client->request('GET', '/api/darkwood/archives');
        self::assertSame(403, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('premium_required', $data['error'] ?? null);
        self::assertSame('Premium access required', $data['message'] ?? null);
    }

    public function testArchivesPremiumKeyReturns200(): void
    {
        $client = $this->createApiClientWithBetaKey(isPremium: true, dailyLimit: 10);

        $client->request('GET', '/api/darkwood/archives');
        self::assertSame(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame(['archives' => []], $data);
    }

    public function testInvalidJsonReturns400(): void
    {
        $client = $this->createApiClientWithBetaKey(isPremium: false, dailyLimit: 10);

        $client->request('POST', '/api/darkwood/action', [], [], [], '{"query":');

        self::assertSame(400, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('bad_request', $data['error'] ?? null);
        self::assertSame('Could not decode request body.', $data['message'] ?? null);
    }

    private function createBetaKeyAndGetRaw(KernelBrowser $client, bool $isPremium, ?int $dailyLimit): string
    {
        $rawKey = bin2hex(random_bytes(16));
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $apiKey = new ApiKey();
        $apiKey->setKeyHash(hash('sha256', $rawKey));
        $apiKey->setName('monetization-test');
        $apiKey->setIsActive(true);
        $apiKey->setIsBeta(true);
        $apiKey->setIsPremium($isPremium);
        $apiKey->setDailyActionLimit($dailyLimit);

        try {
            $em->persist($apiKey);
            $em->flush();
        } catch (Throwable $e) {
            self::markTestSkipped('Database not available for API key: ' . $e->getMessage());
        }

        return $rawKey;
    }

    private function createApiClientWithBetaKey(bool $isPremium = false, ?int $dailyLimit = 10): KernelBrowser
    {
        $client = $this->getHostClient();
        $rawKey = $this->createBetaKeyAndGetRaw($client, $isPremium, $dailyLimit);
        $client->setServerParameter('HTTP_X_API_KEY', $rawKey);

        return $client;
    }
}
