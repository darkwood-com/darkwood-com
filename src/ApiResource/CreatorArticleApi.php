<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\CreatorArticleProcessor;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/creator-articles',
            security: "is_granted('ROLE_ADMIN')",
            read: false,
            name: 'api_creator_articles_create',
            processor: CreatorArticleProcessor::class,
        ),
    ],
)]
final class CreatorArticleApi {}
