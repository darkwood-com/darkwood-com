<?php

declare(strict_types=1);

namespace App\Enum;

enum ArticleType: string
{
    case Manual = 'manual';
    case Auto = 'auto';
}
