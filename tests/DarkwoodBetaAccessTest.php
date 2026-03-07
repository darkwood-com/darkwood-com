<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\ApiKey;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

/**
 * Beta access: Darkwood API endpoints require valid X-API-Key with active + beta.
 * Tests are independent of dev/prod env.
 */
class DarkwoodBetaAccessTest extends CommonWebTestCase
{
    public function getHostParameter(): string
    {
        return 'api_host';
    }

    public function testGetStateWithoutKeyReturns401(): void
    {
        $client = $this->getHostClient();
        $client->request('GET', '/api/darkwood/state');

        self::assertSame(401, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('A valid API key is required', $data['error'] ?? null);
    }

    public function testGetStateWithInvalidKeyReturns401(): void
    {
        $client = $this->getHostClient();
        $client->request('GET', '/api/darkwood/state', [], [], [
            'HTTP_X_API_KEY' => 'invalid-key-never-in-db',
        ]);

        self::assertSame(401, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('A valid API key is required', $data['error'] ?? null);
    }

    public function testGetStateWithInactiveKeyReturns403(): void
    {
        $client = $this->getHostClient();
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $rawKey = bin2hex(random_bytes(16));
        $apiKey = new ApiKey();
        $apiKey->setKeyHash(hash('sha256', $rawKey));
        $apiKey->setName('inactive-test');
        $apiKey->setIsActive(false);
        $apiKey->setIsBeta(true);
        $apiKey->setIsPremium(false);

        try {
            $em->persist($apiKey);
            $em->flush();
        } catch (Throwable $e) {
            self::markTestSkipped('Database not available: ' . $e->getMessage());
        }

        try {
            $client->request('GET', '/api/darkwood/state', [], [], [
                'HTTP_X_API_KEY' => $rawKey,
            ]);
            self::assertSame(403, $client->getResponse()->getStatusCode());
            $data = json_decode($client->getResponse()->getContent(), true);
            self::assertSame('API key is inactive', $data['error'] ?? null);
        } finally {
            $em->remove($apiKey);
            $em->flush();
        }
    }

    public function testGetStateWithActiveButNotBetaKeyReturns403(): void
    {
        $client = $this->getHostClient();
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $rawKey = bin2hex(random_bytes(16));
        $apiKey = new ApiKey();
        $apiKey->setKeyHash(hash('sha256', $rawKey));
        $apiKey->setName('not-beta-test');
        $apiKey->setIsActive(true);
        $apiKey->setIsBeta(false);
        $apiKey->setIsPremium(false);

        try {
            $em->persist($apiKey);
            $em->flush();
        } catch (Throwable $e) {
            self::markTestSkipped('Database not available: ' . $e->getMessage());
        }

        try {
            $client->request('GET', '/api/darkwood/state', [], [], [
                'HTTP_X_API_KEY' => $rawKey,
            ]);
            self::assertSame(403, $client->getResponse()->getStatusCode());
            $data = json_decode($client->getResponse()->getContent(), true);
            self::assertSame('Beta access required', $data['error'] ?? null);
        } finally {
            $em->remove($apiKey);
            $em->flush();
        }
    }

    public function testGetStateWithBetaKeyReturns200(): void
    {
        $client = $this->getHostClient();
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $rawKey = bin2hex(random_bytes(16));
        $apiKey = new ApiKey();
        $apiKey->setKeyHash(hash('sha256', $rawKey));
        $apiKey->setName('beta-test');
        $apiKey->setIsActive(true);
        $apiKey->setIsBeta(true);
        $apiKey->setIsPremium(false);

        try {
            $em->persist($apiKey);
            $em->flush();
        } catch (Throwable $e) {
            self::markTestSkipped('Database not available: ' . $e->getMessage());
        }

        try {
            $client->request('GET', '/api/darkwood/state', [], [], [
                'HTTP_X_API_KEY' => $rawKey,
            ]);
            self::assertSame(200, $client->getResponse()->getStatusCode());
        } finally {
            $em->remove($apiKey);
            $em->flush();
        }
    }

    public function testPostActionWithBetaKeyReturns200(): void
    {
        $client = $this->getHostClient();
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $rawKey = bin2hex(random_bytes(16));
        $apiKey = new ApiKey();
        $apiKey->setKeyHash(hash('sha256', $rawKey));
        $apiKey->setName('beta-post-test');
        $apiKey->setIsActive(true);
        $apiKey->setIsBeta(true);
        $apiKey->setIsPremium(false);

        try {
            $em->persist($apiKey);
            $em->flush();
        } catch (Throwable $e) {
            self::markTestSkipped('Database not available: ' . $e->getMessage());
        }

        try {
            $client->request('POST', '/api/darkwood/action', [], [], [
                'HTTP_X_API_KEY' => $rawKey,
            ], '{"query":{"state":"main"}}');
            self::assertSame(200, $client->getResponse()->getStatusCode());
        } finally {
            $em->remove($apiKey);
            $em->flush();
        }
    }
}
