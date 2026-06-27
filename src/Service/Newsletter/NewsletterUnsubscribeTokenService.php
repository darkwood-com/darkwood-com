<?php

declare(strict_types=1);

namespace App\Service\Newsletter;

use function strlen;

/**
 * @author Mathieu
 */
final readonly class NewsletterUnsubscribeTokenService
{
    private const int TOKEN_LENGTH = 32;

    public function __construct(private string $secret) {}

    public function generate(int $userId, string $email): string
    {
        return substr($this->hash($userId, $email), 0, self::TOKEN_LENGTH);
    }

    public function isValid(int $userId, string $email, string $token): bool
    {
        if ('' === $token || strlen($token) !== self::TOKEN_LENGTH) {
            return false;
        }

        return hash_equals($this->generate($userId, $email), $token);
    }

    private function hash(int $userId, string $email): string
    {
        return hash_hmac('sha256', $userId . '|' . strtolower(trim($email)), $this->secret);
    }
}
