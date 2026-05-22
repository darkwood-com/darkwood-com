<?php

declare(strict_types=1);

namespace App\Enum;

enum ArticleType: string
{
    case Manual = 'manual';
    case Auto = 'auto';

    public function blogListRouteName(): string
    {
        return match ($this) {
            self::Manual => 'blog_home',
            self::Auto => 'blog_auto',
        };
    }
}
