<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\ApiKey;
use App\Entity\DarkwoodArchive;
use App\Repository\DarkwoodArchiveRepository;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Throwable;

/**
 * Premium archives: list, get by id, 404, payload shape, creation command.
 */
class DarkwoodArchivesTest extends CommonWebTestCase
{
    public function getHostParameter(): string
    {
        return 'api_host';
    }

    public function testPremiumKeyCanFetchOneArchive(): void
    {
        $client = $this->createApiClientWithPremiumKey();
        $this->createArchive($client, '2026-03-08', ['state' => 'main', 'data' => []]);

        $client->request('GET', '/api/darkwood/archives/2026-03-08');
        self::assertSame(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($data);
        self::assertArrayHasKey('state', $data);
        self::assertSame('main', $data['state']);
    }

    public function testUnknownArchiveIdReturns404(): void
    {
        $client = $this->createApiClientWithPremiumKey();

        $client->request('GET', '/api/darkwood/archives/1999-01-01');
        self::assertSame(404, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('archive_not_found', $data['error'] ?? null);
        self::assertSame('Archive not found', $data['message'] ?? null);
    }

    public function testArchivePayloadShapeIsStable(): void
    {
        $client = $this->createApiClientWithPremiumKey();
        $payload = ['state' => 'not-logged', 'user' => null, 'data' => []];
        $this->createArchive($client, '2026-02-01', $payload);

        $client->request('GET', '/api/darkwood/archives/2026-02-01');
        self::assertSame(200, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame($payload, $data);
    }

    public function testFreeKeyGets403OnArchiveGet(): void
    {
        $client = $this->createApiClientWithBetaKey(isPremium: false, dailyLimit: 10);

        $client->request('GET', '/api/darkwood/archives/2026-03-07');
        self::assertSame(403, $client->getResponse()->getStatusCode());

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('premium_required', $data['error'] ?? null);
    }

    public function testArchiveCreationPersistence(): void
    {
        $client = $this->getHostClient();
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $repo = $em->getRepository(DarkwoodArchive::class);
        if ($repo instanceof DarkwoodArchiveRepository) {
            $existing = $repo->findOneByDateId('2026-01-15');
            if ($existing !== null) {
                $em->remove($existing);
                $em->flush();
            }
        }

        $date = new DateTimeImmutable('2026-01-15', new DateTimeZone('UTC'));
        $archive = new DarkwoodArchive();
        $archive->setArchiveDate($date);
        $archive->setPayload(['state' => 'main']);
        $archive->setCreatedAt(new DateTimeImmutable('now', new DateTimeZone('UTC')));

        try {
            $em->persist($archive);
            $em->flush();
        } catch (Throwable $e) {
            self::markTestSkipped('Database not available: ' . $e->getMessage());
        }

        $rawKey = bin2hex(random_bytes(16));
        $apiKey = new ApiKey();
        $apiKey->setKeyHash(hash('sha256', $rawKey));
        $apiKey->setName('persistence-test');
        $apiKey->setIsActive(true);
        $apiKey->setIsBeta(true);
        $apiKey->setIsPremium(true);
        $apiKey->setDailyActionLimit(10);
        $em->persist($apiKey);
        $em->flush();

        $client->setServerParameter('HTTP_X_API_KEY', $rawKey);
        $client->request('GET', '/api/darkwood/archives');
        $list = json_decode($client->getResponse()->getContent(), true);
        $ids = array_column($list['archives'] ?? [], 'id');
        self::assertContains('2026-01-15', $ids);

        $client->request('GET', '/api/darkwood/archives/2026-01-15');
        self::assertSame(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('main', $data['state'] ?? null);

        $archiveToRemove = $em->find(DarkwoodArchive::class, $archive->getId());
        if ($archiveToRemove !== null) {
            $em->remove($archiveToRemove);
        }
        $apiKeyToRemove = $em->find(ApiKey::class, $apiKey->getId());
        if ($apiKeyToRemove !== null) {
            $em->remove($apiKeyToRemove);
        }
        $em->flush();
    }

    private function createApiClientWithPremiumKey(): KernelBrowser
    {
        return $this->createApiClientWithBetaKey(isPremium: true, dailyLimit: 10);
    }

    private function createApiClientWithBetaKey(bool $isPremium = false, ?int $dailyLimit = 10): KernelBrowser
    {
        $client = $this->getHostClient();
        $rawKey = $this->createBetaKeyAndGetRaw($client, $isPremium, $dailyLimit);
        $client->setServerParameter('HTTP_X_API_KEY', $rawKey);

        return $client;
    }

    private function createBetaKeyAndGetRaw(KernelBrowser $client, bool $isPremium, ?int $dailyLimit): string
    {
        $rawKey = bin2hex(random_bytes(16));
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $apiKey = new ApiKey();
        $apiKey->setKeyHash(hash('sha256', $rawKey));
        $apiKey->setName('archives-test');
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

    private function createArchive(KernelBrowser $client, string $dateId, array $payload): void
    {
        $container = $client->getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $repo = $em->getRepository(DarkwoodArchive::class);
        if ($repo instanceof DarkwoodArchiveRepository) {
            $existing = $repo->findOneByDateId($dateId);
            if ($existing !== null) {
                $em->remove($existing);
                $em->flush();
            }
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateId)->setTime(0, 0, 0);
        $archive = new DarkwoodArchive();
        $archive->setArchiveDate($date);
        $archive->setPayload($payload);
        $archive->setCreatedAt(new DateTimeImmutable('now', new DateTimeZone('UTC')));

        try {
            $em->persist($archive);
            $em->flush();
        } catch (Throwable $e) {
            self::markTestSkipped('Database not available: ' . $e->getMessage());
        }
    }
}
