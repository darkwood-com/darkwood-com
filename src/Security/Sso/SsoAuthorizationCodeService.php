<?php

declare(strict_types=1);

namespace App\Security\Sso;

use Psr\Cache\CacheItemPoolInterface;

use function sprintf;

/**
 * @author Mathieu Ledru
 */
final readonly class SsoAuthorizationCodeService
{
    private const int TTL_SECONDS = 300;

    public function __construct(
        private CacheItemPoolInterface $cache,
    ) {}

    /**
     * @param array{id: int|string, email: string, roles: list<string>} $user
     */
    public function issueCode(array $user, string $clientId, string $redirectUri, string $audience): string
    {
        $code = bin2hex(random_bytes(32));
        $item = $this->cache->getItem($this->cacheKey($code));
        $item->set([
            'user' => $user,
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'audience' => $audience,
        ]);
        $item->expiresAfter(self::TTL_SECONDS);
        $this->cache->save($item);

        return $code;
    }

    /**
     * @return array{
     *     user: array{id: int|string, email: string, roles: list<string>},
     *     client_id: string,
     *     redirect_uri: string,
     *     audience: string
     * }|null
     */
    public function consumeCode(string $code): ?array
    {
        $item = $this->cache->getItem($this->cacheKey($code));
        if (!$item->isHit()) {
            return null;
        }

        $payload = $item->get();
        $this->cache->deleteItem($item->getKey());

        return is_array($payload) ? $payload : null;
    }

    private function cacheKey(string $code): string
    {
        return sprintf('sso_auth_code.%s', $code);
    }
}
