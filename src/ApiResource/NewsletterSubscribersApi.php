<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\Provider\NewsletterSubscribersProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/newsletter-subscribers',
            provider: NewsletterSubscribersProvider::class,
            security: "is_granted('ROLE_ADMIN')",
            name: 'api_newsletter_subscribers',
        ),
    ],
)]
final class NewsletterSubscribersApi {}
