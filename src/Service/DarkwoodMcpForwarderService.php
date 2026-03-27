<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;

use function json_decode;

/**
 * Forwards MCP tool calls to the Darkwood API via sub-requests so auth (X-API-Key, etc.) is applied.
 */
final readonly class DarkwoodMcpForwarderService
{
    public function __construct(
        private HttpKernelInterface $kernel,
        private RequestStack $requestStack,
        private RouterInterface $router,
    ) {}

    /**
     * Run a sub-request to the Darkwood API and return the JSON-decoded response body.
     *
     * @return array<string, mixed>|mixed
     */
    public function forward(string $routeName, string $method = 'GET', ?array $body = null, array $routeParams = []): mixed
    {
        $url = $this->router->generate($routeName, $routeParams, RouterInterface::ABSOLUTE_PATH);
        $current = $this->requestStack->getCurrentRequest();

        $content = null;
        if ($body !== null && $method === 'POST') {
            $content = json_encode($body);
        }

        $subRequest = Request::create($url, $method, [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_CONTENT_TYPE' => 'application/json',
        ], $content);

        if ($current !== null) {
            if ($current->headers->has(ApiKeyResolverService::HEADER_NAME)) {
                $subRequest->headers->set(ApiKeyResolverService::HEADER_NAME, $current->headers->get(ApiKeyResolverService::HEADER_NAME));
            }
            if ($current->headers->has('Authorization')) {
                $subRequest->headers->set('Authorization', $current->headers->get('Authorization'));
            }
        }

        $response = $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $responseContent = $response->getContent();
        if ($responseContent === false || $responseContent === '') {
            return null;
        }

        $decoded = json_decode($responseContent, true);

        return $decoded !== null ? $decoded : $responseContent;
    }
}
