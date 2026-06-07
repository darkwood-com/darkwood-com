<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\AutoArticleProcessor;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/auto-articles',
            security: "is_granted('ROLE_ADMIN')",
            read: false,
            name: 'api_auto_articles_create',
            processor: AutoArticleProcessor::class,
        ),
    ],
)]
final class AutoArticleApi {}
