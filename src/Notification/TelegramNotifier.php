<?php

declare(strict_types=1);

namespace App\Notification;

use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Throwable;

use function str_starts_with;

/**
 * @author Mathieu
 */
final readonly class TelegramNotifier
{
    private const string TRANSPORT = 'telegram';

    public function __construct(
        private ChatterInterface $chatter,
        private string $telegramDsn = 'null://null',
    ) {}

    public function isConfigured(): bool
    {
        return str_starts_with($this->telegramDsn, 'telegram://');
    }

    public function notify(string $message): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $chatMessage = new ChatMessage(htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        $chatMessage->transport(self::TRANSPORT);
        $chatMessage->options(
            (new TelegramOptions())
                ->disableWebPagePreview(true)
                ->parseMode(TelegramOptions::PARSE_MODE_HTML),
        );

        $this->chatter->send($chatMessage);

        return true;
    }

    public function notifySafely(string $message, ?callable $onFailure = null): bool
    {
        try {
            return $this->notify($message);
        } catch (Throwable $throwable) {
            if (null !== $onFailure) {
                $onFailure($throwable);
            }

            return false;
        }
    }
}
