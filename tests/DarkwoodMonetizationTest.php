<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\ApiKey;
use App\Entity\Entitlement;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Throwable;

/**
 * Darkwood API monetization: rate limiting (prod only) and premium-only archives.
 * - Test env: no rate limit, archives allowed for all.
 * - Simulated prod: anonymous rate limit, premium bypass, archives 403/200.
 * All requests use a beta API key so they pass the Darkwood beta gate.
 */
class DarkwoodMonetizationTest extends WebTestCase
{
    private const API_HOST = 'api.darkwood.localhost';

    /**
     * In test env monetization is disabled: POST action is not rate limited.
     */
    public function testPostActionNotRateLimitedInTestEnv(): void
    {
        $client = $this->createApiClientWithBetaKey();

        for ($i = 0; $i < 5; $i++) {
            $client->request('POST', '/api/darkwood/action', [], [], [], '{"query":{"state":"main"}}');
            self::assertNotSame(429, $client->getResponse()->getStatusCode(), 'POST action should not be rate limited in test env');
        }
    }

    /**
     * Simulated prod (DARKWOOD_MONETIZATION_SIMULATE_PROD): anonymous user hits rate limit after 60 actions.
     *
     * @group slow
     */
    public function testAnonymousUserHitsRateLimitInProd(): void
    {
        $prev = getenv('DARKWOOD_MONETIZATION_SIMULATE_PROD');
        putenv('DARKWOOD_MONETIZATION_SIMULATE_PROD=1');

        try {
            $client = $this->createApiClientWithBetaKey();
            $container = $client->getContainer();
            /** @var RateLimiterFactory $anonymousFactory */
            $anonymousFactory = $container->get('limiter.darkwood_action_anonymous');
            $limiter = $anonymousFactory->create('anon_127.0.0.1');
            $limiter->reset();

            for ($i = 0; $i < 60; $i++) {
                $client->request('POST', '/api/darkwood/action', [], [], [], '{"query":{"state":"main"}}');
                self::assertSame(200, $client->getResponse()->getStatusCode(), 'Request ' . ($i + 1) . ' should succeed');
            }

            $client->request('POST', '/api/darkwood/action', [], [], [], '{"query":{"state":"main"}}');
            self::assertSame(429, $client->getResponse()->getStatusCode(), '61st request should be rate limited');

            $data = json_decode($client->getResponse()->getContent(), true);
            self::assertArrayHasKey('error', $data);
            self::assertSame('rate_limited', $data['error']);
            self::assertArrayHasKey('retryAfter', $data);
        } finally {
            putenv($prev !== false ? 'DARKWOOD_MONETIZATION_SIMULATE_PROD=' . $prev : 'DARKWOOD_MONETIZATION_SIMULATE_PROD');
        }
    }

    /**
     * Simulated prod: non-premium user gets 403 on GET /api/darkwood/archives.
     */
    public function testNonPremiumUserGets403OnArchivesInProd(): void
    {
        $prev = getenv('DARKWOOD_MONETIZATION_SIMULATE_PROD');
        putenv('DARKWOOD_MONETIZATION_SIMULATE_PROD=1');

        try {
            $client = $this->createApiClientWithBetaKey();

            $client->request('GET', '/api/darkwood/archives');
            self::assertSame(403, $client->getResponse()->getStatusCode(), 'Archives should require premium when monetization enabled');

            $data = json_decode($client->getResponse()->getContent(), true);
            self::assertArrayHasKey('error', $data);
            self::assertSame('forbidden', $data['error']);
        } finally {
            putenv($prev !== false ? 'DARKWOOD_MONETIZATION_SIMULATE_PROD=' . $prev : 'DARKWOOD_MONETIZATION_SIMULATE_PROD');
        }
    }

    /**
     * In test env everyone is premium: GET /api/darkwood/archives returns 200.
     */
    public function testArchivesAllowedInTestEnv(): void
    {
        $client = $this->createApiClientWithBetaKey();

        $client->request('GET', '/api/darkwood/archives');
        self::assertSame(200, $client->getResponse()->getStatusCode(), 'Archives should be allowed in test env (everyone premium)');

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($data);
    }

    /**
     * Simulated prod: premium user gets 200 on GET /api/darkwood/archives.
     * Requires test database (e.g. darkwood_test) to be available.
     */
    public function testPremiumUserGets200OnArchivesInProd(): void
    {
        putenv('DARKWOOD_MONETIZATION_SIMULATE_PROD=1');
        $client = $this->createApiClientWithBetaKey();
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('premium-test@darkwood.test');
        $user->setUsername('premium_test_user');
        $user->setPassword($hasher->hashPassword($user, 'testpass'));

        try {
            $em->persist($user);
            $em->flush();
        } catch (Throwable $e) {
            self::markTestSkipped('Test database not available: ' . $e->getMessage());
        }

        $entitlement = new Entitlement();
        $entitlement->setUser($user);
        $entitlement->setPlan(Entitlement::PLAN_PREMIUM);
        $entitlement->setActive(true);
        $em->persist($entitlement);
        $em->flush();

        $client->request('POST', '/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'premium-test@darkwood.test',
            'password' => 'testpass',
        ]));
        self::assertSame(200, $client->getResponse()->getStatusCode(), 'Auth should succeed');
        $token = json_decode($client->getResponse()->getContent(), true)['token'] ?? null;
        self::assertNotNull($token, 'JWT token should be returned');

        $client->request('GET', '/api/darkwood/archives', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        self::assertSame(200, $client->getResponse()->getStatusCode(), 'Premium user should get 200 on archives');

        $em->remove($entitlement);
        $em->remove($user);
        $em->flush();

        putenv('DARKWOOD_MONETIZATION_SIMULATE_PROD');
    }

    private function createApiClient(array $options = []): KernelBrowser
    {
        $client = static::createClient($options);
        $client->setServerParameters([
            'HTTP_HOST' => self::API_HOST,
            'CONTENT_TYPE' => 'application/json',
        ]);

        return $client;
    }

    /** Create a beta API key and return raw key; client must send it via X-API-Key. */
    private function createBetaKeyAndGetRaw(KernelBrowser $client): string
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
        $apiKey->setIsPremium(false);
        $em->persist($apiKey);
        $em->flush();

        return $rawKey;
    }

    private function createApiClientWithBetaKey(array $options = []): KernelBrowser
    {
        $client = $this->createApiClient($options);

        try {
            $rawKey = $this->createBetaKeyAndGetRaw($client);
        } catch (Throwable $e) {
            self::markTestSkipped('Database not available for beta key: ' . $e->getMessage());
        }
        $client->setServerParameters(array_merge($client->getServerParameters(), [
            'HTTP_X_API_KEY' => $rawKey,
        ]));

        return $client;
    }
}
