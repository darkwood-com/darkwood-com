<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Enum\ArticleReactionEmoji;
use App\Repository\ArticleRepository;
use App\Service\ArticleReactionService;
use JsonException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use function is_string;
use function preg_match;
use function sprintf;
use function trim;

final readonly class ArticleReactionProcessor implements ProcessorInterface
{
    public function __construct(
        private ArticleReactionService $articleReactionService,
        private ArticleRepository $articleRepository,
        private TokenStorageInterface $tokenStorage,
        private RequestStack $requestStack,
    ) {}

    /**
     * @return array{counts: array<string, int>, userReactions: list<string>, active: bool}
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            throw new BadRequestHttpException('No current request.');
        }

        try {
            $payload = $request->toArray();
        } catch (JsonException) {
            throw new BadRequestHttpException('Request body must be valid JSON.');
        }

        $articleId = $this->resolveArticleId($payload['article'] ?? null);
        $emoji = ArticleReactionEmoji::tryFromRequest(
            is_string($payload['emoji'] ?? null) ? $payload['emoji'] : null,
        );
        if (!$emoji instanceof ArticleReactionEmoji) {
            throw new BadRequestHttpException('Field "emoji" is required and must be a valid reaction emoji.');
        }

        $article = $this->articleRepository->find($articleId);
        if ($article === null) {
            throw new NotFoundHttpException(sprintf('Article with id "%d" was not found.', $articleId));
        }

        $user = $this->getCurrentUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException('Authentication required.');
        }

        return $this->articleReactionService->addReaction($article, $emoji, $user);
    }

    private function getCurrentUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        return $user instanceof User ? $user : null;
    }

    private function resolveArticleId(mixed $articleReference): int
    {
        if (!is_string($articleReference) || '' === trim($articleReference)) {
            throw new BadRequestHttpException('Field "article" is required.');
        }

        if (preg_match('/\/api\/articles\/(\d+)/', $articleReference, $matches)) {
            return (int) $matches[1];
        }

        if (ctype_digit(trim($articleReference))) {
            return (int) trim($articleReference);
        }

        throw new BadRequestHttpException('Field "article" must be an article IRI or numeric id.');
    }
}
