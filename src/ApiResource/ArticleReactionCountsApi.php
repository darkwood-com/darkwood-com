<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\Provider\ArticleReactionCountsProvider;

/**
 * Read-only reaction counts for a published article, exposed to admin-only tooling
 * (e.g. Navi's editorial reporting) so it can pull feedback without paid analytics APIs.
 *
 * @author Mathieu Ledru
 */
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/article-reaction-counts/by-generation-id/{generationId}',
            uriVariables: ['generationId'],
            security: "is_granted('ROLE_ADMIN')",
            name: 'api_article_reaction_counts_by_generation_id',
            provider: ArticleReactionCountsProvider::class,
        ),
        new Get(
            uriTemplate: '/article-reaction-counts/by-slug/{locale}/{slug}',
            uriVariables: ['locale', 'slug'],
            security: "is_granted('ROLE_ADMIN')",
            name: 'api_article_reaction_counts_by_slug',
            provider: ArticleReactionCountsProvider::class,
        ),
    ],
)]
final class ArticleReactionCountsApi {}
