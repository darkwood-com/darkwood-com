<?php

declare(strict_types=1);

namespace App\State;

use App\Dto\ListArticlesInput;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * MCP processor: returns a list of Articles (optionally limited and filtered by locale).
 */
final readonly class ListArticlesProcessor implements ProcessorInterface
{
    public function __construct(
        private ArticleRepository $articleRepository,
    ) {}

    /**
     * @return iterable<Article>
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $limit = 30;
        $locale = 'en';

        if ($data instanceof ListArticlesInput) {
            if ($data->limit !== null && $data->limit > 0) {
                $limit = min($data->limit, 100);
            }
            if ($data->locale !== null && $data->locale !== '') {
                $locale = $data->locale;
            }
        }

        $qb = $this->articleRepository->findActivesQueryBuilder($locale, $limit);

        /** @var Paginator<Article> $paginator */
        $paginator = new Paginator($qb->getQuery());

        return $paginator;
    }
}
