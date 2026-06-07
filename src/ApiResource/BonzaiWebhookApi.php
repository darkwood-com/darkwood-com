<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\BonzaiWebhookProcessor;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/bonzai/webhook',
            security: "is_granted('ROLE_ADMIN')",
            read: false,
            deserialize: false,
            name: 'api_bonzai_webhook',
            processor: BonzaiWebhookProcessor::class,
        ),
    ],
)]
final class BonzaiWebhookApi {}
