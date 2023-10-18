<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraints as Assert;

class PaginationDTO
{
    public function __construct(
        #[Assert\GreaterThanOrEqual(1)]
        public readonly int $page = 1,

		#[Assert\Regex('/^[a-z]+$/')]
		public readonly string $sort = 'id',
    ) {
    }
}
