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
            security: "is_granted('ROLE_ADMIN')",
            name: 'api_newsletter_subscribers',
            provider: NewsletterSubscribersProvider::class,
        ),
    ],
)]
final class NewsletterSubscribersApi {}
