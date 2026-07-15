<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Entity\User;
use App\Security\Sso\SsoAccessTokenFactory;
use App\Security\Sso\SsoAuthorizationCodeService;
use App\Security\Sso\SsoClientRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use function is_string;

/**
 * Generic authorization-code SSO for trusted Darkwood applications.
 *
 * @author Mathieu Ledru
 */
final class SsoAuthController extends AbstractController
{
    public function __construct(
        private readonly SsoClientRegistry $clients,
        private readonly SsoAuthorizationCodeService $authorizationCodes,
        private readonly SsoAccessTokenFactory $accessTokens,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('/sso/authorize', name: 'sso_authorize')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function authorize(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new Response('Authentication required.', Response::HTTP_UNAUTHORIZED);
        }

        $clientId = $request->query->getString('client_id');
        $redirectUri = $request->query->getString('redirect_uri');
        if ('' === $clientId || '' === $redirectUri) {
            return new Response('client_id and redirect_uri are required.', Response::HTTP_BAD_REQUEST);
        }

        $client = $this->clients->get($clientId);
        if (null === $client || !$client->allowsRedirectUri($redirectUri)) {
            return new Response('Invalid client or redirect URI.', Response::HTTP_BAD_REQUEST);
        }

        $code = $this->authorizationCodes->issueCode([
            'id' => $user->getId(),
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
        ], $clientId, $redirectUri, $client->audience);

        $separator = str_contains($redirectUri, '?') ? '&' : '?';

        return new RedirectResponse($redirectUri . $separator . 'code=' . urlencode($code));
    }

    #[Route('/api/sso/token', name: 'sso_token', methods: ['POST'])]
    public function token(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $payload */
        $payload = json_decode($request->getContent(), true) ?? [];
        $code = is_string($payload['code'] ?? null) ? $payload['code'] : '';
        $clientId = is_string($payload['client_id'] ?? null) ? $payload['client_id'] : '';
        $redirectUri = is_string($payload['redirect_uri'] ?? null) ? $payload['redirect_uri'] : '';

        if ('' === $code || '' === $clientId || '' === $redirectUri) {
            return new JsonResponse(['error' => 'invalid_request'], Response::HTTP_BAD_REQUEST);
        }

        $client = $this->clients->get($clientId);
        if (null === $client || !$client->allowsRedirectUri($redirectUri)) {
            return new JsonResponse(['error' => 'invalid_client'], Response::HTTP_BAD_REQUEST);
        }

        $stored = $this->authorizationCodes->consumeCode($code);
        if (null === $stored) {
            $this->logger->warning('sso.token.invalid_grant', [
                'reason' => 'code_missing_or_expired',
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
            ]);

            return new JsonResponse(['error' => 'invalid_grant'], Response::HTTP_BAD_REQUEST);
        }

        if (
            $stored['client_id'] !== $clientId
            || $stored['redirect_uri'] !== $redirectUri
            || $stored['audience'] !== $client->audience
        ) {
            $this->logger->warning('sso.token.invalid_grant', [
                'reason' => 'code_mismatch',
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'stored_client_id' => $stored['client_id'],
                'stored_redirect_uri' => $stored['redirect_uri'],
                'stored_audience' => $stored['audience'],
                'expected_audience' => $client->audience,
            ]);

            return new JsonResponse(['error' => 'invalid_grant'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail((string) $stored['user']['email']);
        $user->setRoles($stored['user']['roles']);

        $jwt = $this->accessTokens->create($user, $client->audience, $stored['user']['roles'], $clientId);

        return new JsonResponse([
            'access_token' => $jwt['token'],
            'token_type' => 'Bearer',
            'expires_in' => $jwt['expires_in'],
            'audience' => $client->audience,
            'user' => $stored['user'],
        ]);
    }

    #[Route('/api/sso/me', name: 'sso_me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
        ]);
    }
}
