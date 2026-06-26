<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Contact;
use App\Notification\ContactTelegramMessage;
use App\Notification\TelegramNotifier;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @author Mathieu
 */
final readonly class ContactSubmissionNotifier
{
    public function __construct(
        private TelegramNotifier $telegramNotifier,
        private LoggerInterface $logger,
    ) {}

    public function notify(Contact $contact, string $siteRef): void
    {
        $this->telegramNotifier->notifySafely(
            ContactTelegramMessage::format($contact, $siteRef),
            fn (Throwable $throwable) => $this->logger->warning('contact.telegram_failed', [
                'contact_id' => $contact->getId(),
                'site_ref' => $siteRef,
                'error' => $throwable->getMessage(),
            ]),
        );
    }
}
