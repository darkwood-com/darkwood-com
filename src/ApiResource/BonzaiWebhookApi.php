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
            processor: BonzaiWebhookProcessor::class,
            read: false,
            deserialize: false,
            security: "is_granted('ROLE_ADMIN')",
            name: 'api_bonzai_webhook',
        ),
    ],
)]
final class BonzaiWebhookApi {}
