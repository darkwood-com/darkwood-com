<?php

declare(strict_types=1);

namespace App\Enum;

enum ArticleType: string
{
    case Manual = 'manual';
    case Watch = 'watch';
    case Release = 'release';
    case Creator = 'creator';

    public function blogListRouteName(): string
    {
        return match ($this) {
            self::Manual => 'blog_home',
            self::Watch => 'blog_watch',
            self::Release => 'blog_release',
            self::Creator => 'blog_creator',
        };
    }
}
