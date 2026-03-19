<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ArticleIdInput;
use App\Entity\Article;
use App\Repository\ArticleRepository;

/**
 * MCP processor: returns a single Article by id.
 */
final readonly class GetArticleProcessor implements ProcessorInterface
{
    public function __construct(
        private ArticleRepository $articleRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?Article
    {
        if (!$data instanceof ArticleIdInput) {
            return null;
        }

        $article = $this->articleRepository->findOneToEdit($data->id);

        return $article instanceof Article ? $article : null;
    }
}
