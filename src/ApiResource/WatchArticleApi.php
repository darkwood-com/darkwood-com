<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\WatchArticleProcessor;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/watch-articles',
            security: "is_granted('ROLE_ADMIN')",
            read: false,
            name: 'api_watch_articles_create',
            processor: WatchArticleProcessor::class,
        ),
    ],
)]
final class WatchArticleApi {}
