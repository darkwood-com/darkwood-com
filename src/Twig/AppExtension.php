<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class AppExtension extends AbstractExtension
{
    public const MARKDOWN_MAX_CHARS = 100_000;

    public function getFilters(): array
    {
        return [
            new TwigFilter('article_excerpt', [AppRuntime::class, 'articleExcerpt']),
            new TwigFilter('limit_markdown_source', [AppRuntime::class, 'limitMarkdownSource']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('seo_data', [AppRuntime::class, 'seoData']),
            new TwigFunction('hreflang_data', [AppRuntime::class, 'hreflangData']),
            new TwigFunction('page_links', [AppRuntime::class, 'pageLinks']),
            new TwigFunction('app_menu_links', [AppRuntime::class, 'appMenuLinks']),
        ];
    }
}
