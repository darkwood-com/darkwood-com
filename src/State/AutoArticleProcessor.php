<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Article;
use App\Entity\ArticleTranslation;
use App\Enum\ArticleType;
use App\Repository\ArticleRepository;
use App\Service\BlogArticleService;
use JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use function is_array;
use function is_bool;
use function is_string;
use function sprintf;
use function trim;

final readonly class AutoArticleProcessor implements ProcessorInterface
{
    public function __construct(
        private ArticleRepository $articles,
        private BlogArticleService $articleService,
        private RequestStack $requestStack,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            throw new BadRequestHttpException('No current request.');
        }

        try {
            $payload = $request->toArray();
        } catch (JsonException) {
            throw new BadRequestHttpException('Request body must be valid JSON.');
        }

        if (!isset($payload['generation_id']) || !is_string($payload['generation_id']) || '' === trim($payload['generation_id'])) {
            throw new BadRequestHttpException('Field "generation_id" is required.');
        }

        $translationsPayload = $payload['translations'] ?? null;
        if (!is_array($translationsPayload) || [] === $translationsPayload) {
            // Backward-compatible fallback to legacy flat payload.
            $legacyRequired = ['title', 'slug', 'premium_content'];
            foreach ($legacyRequired as $field) {
                if (!isset($payload[$field]) || !is_string($payload[$field]) || '' === trim($payload[$field])) {
                    throw new BadRequestHttpException(sprintf('Field "%s" is required.', $field));
                }
            }

            $content = $payload['content'] ?? $payload['premium_content'];
            if (!is_string($content) || '' === trim($content)) {
                $content = (string) $payload['premium_content'];
            }

            $translationsPayload = [[
                'locale' => 'en',
                'title' => $payload['title'],
                'slug' => $payload['slug'],
                'content' => $content,
                'premium_content' => $payload['premium_content'],
            ]];
        }

        $existing = $this->articles->findOneByGenerationId((string) $payload['generation_id']);
        $article = $existing ?? new Article();
        $article->setType(ArticleType::Auto);

        $isPremium = $payload['is_premium'] ?? true;
        if (!is_bool($isPremium)) {
            throw new BadRequestHttpException('Field "is_premium" must be a boolean.');
        }

        $article->setIsPremium($isPremium);
        $article->setGenerationId((string) $payload['generation_id']);
        $article->setMetadata(is_array($payload['metadata'] ?? null) ? $payload['metadata'] : []);

        foreach ($translationsPayload as $index => $translationData) {
            if (!is_array($translationData)) {
                throw new BadRequestHttpException(sprintf('Translation entry at index %d must be an object.', $index));
            }

            $locale = $translationData['locale'] ?? null;
            $title = $translationData['title'] ?? null;
            $slug = $translationData['slug'] ?? null;
            $content = $translationData['content'] ?? null;
            $premiumContent = $translationData['premium_content'] ?? $translationData['premiumContent'] ?? null;

            if (!is_string($locale) || '' === trim($locale)) {
                throw new BadRequestHttpException(sprintf('Field "translations[%d].locale" is required.', $index));
            }

            if (!is_string($title) || '' === trim($title)) {
                throw new BadRequestHttpException(sprintf('Field "translations[%d].title" is required.', $index));
            }

            if (!is_string($slug) || '' === trim($slug)) {
                throw new BadRequestHttpException(sprintf('Field "translations[%d].slug" is required.', $index));
            }

            if (!is_string($content) || '' === trim($content)) {
                throw new BadRequestHttpException(sprintf('Field "translations[%d].content" is required.', $index));
            }

            if (!is_string($premiumContent) || '' === trim($premiumContent)) {
                throw new BadRequestHttpException(sprintf('Field "translations[%d].premium_content" is required.', $index));
            }

            $translation = null;
            foreach ($article->getTranslations() as $existingTranslation) {
                if ($existingTranslation->getLocale() === $locale) {
                    $translation = $existingTranslation;

                    break;
                }
            }

            if (!$translation instanceof ArticleTranslation) {
                $translation = new ArticleTranslation();
                $translation->setLocale($locale);
                $translation->setActive(true);
                $article->addTranslation($translation);
            }

            $translation->setTitle($title);
            $translation->setSlug($slug);
            $translation->setContent($content);
            $translation->setPremiumContent($premiumContent);
        }

        $this->articleService->save($article);
        $primaryTranslation = $article->getOneTranslation('en');
        if (!$primaryTranslation instanceof ArticleTranslation) {
            $primaryTranslation = $article->getTranslations()->first();
        }

        return [
            'id' => $article->getId(),
            'generation_id' => $article->getGenerationId(),
            'slug' => $primaryTranslation instanceof ArticleTranslation ? $primaryTranslation->getSlug() : null,
            'status' => $existing instanceof Article ? 'updated' : 'created',
        ];
    }
}
