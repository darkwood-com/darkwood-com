<?php

declare(strict_types=1);

namespace App\Notification;

use App\Entity\Contact;
use App\Entity\User;

use function sprintf;

/**
 * @author Mathieu
 */
final class ContactTelegramMessage
{
    public static function format(Contact $contact, string $siteRef): string
    {
        $lines = [
            'New Darkwood contact form submission',
            sprintf('Site: %s', $siteRef),
            sprintf('Email: %s', $contact->getEmail()),
        ];

        $website = $contact->getWebsite();
        if (null !== $website && '' !== trim($website)) {
            $lines[] = sprintf('Website: %s', $website);
        }

        $user = $contact->getUser();
        if ($user instanceof User) {
            $lines[] = sprintf('User: #%d (%s)', (int) $user->getId(), (string) $user->getUsername());
        }

        if (null !== $contact->getId()) {
            $lines[] = sprintf('Contact ID: %d', $contact->getId());
        }

        $lines[] = 'Message:';
        $lines[] = $contact->getContent();

        return implode("\n", $lines);
    }
}
