<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

final class DarkwoodApiExceptionSubscriber implements EventSubscriberInterface
{
    private const PATH_PREFIX = '/api/darkwood/';

    public function __construct(
        private readonly KernelInterface $kernel,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onException', 10],
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), self::PATH_PREFIX)) {
            return;
        }

        $throwable = $event->getThrowable();

        if ($throwable instanceof HttpExceptionInterface) {
            $status = $throwable->getStatusCode();
            $message = $throwable->getMessage() !== ''
                ? $throwable->getMessage()
                : (Response::$statusTexts[$status] ?? 'HTTP error');

            $event->setResponse(new JsonResponse([
                'error' => $this->errorCodeFromStatus($status),
                'message' => $message,
            ], $status, $throwable->getHeaders()));

            return;
        }

        $payload = [
            'error' => 'internal_error',
            'message' => 'An internal error occurred',
        ];

        if ($this->kernel->isDebug()) {
            $payload['exception'] = [
                'message' => $throwable->getMessage(),
                'class' => $throwable::class,
                'trace' => array_map(
                    static function (array $frame): array {
                        return [
                            'file' => $frame['file'] ?? null,
                            'line' => $frame['line'] ?? null,
                            'function' => $frame['function'] ?? null,
                            'class' => $frame['class'] ?? null,
                        ];
                    },
                    $throwable->getTrace()
                ),
            ];
        }

        $event->setResponse(new JsonResponse(
            $payload,
            Response::HTTP_INTERNAL_SERVER_ERROR
        ));
    }

    private function errorCodeFromStatus(int $status): string
    {
        return match ($status) {
            Response::HTTP_BAD_REQUEST => 'bad_request',
            Response::HTTP_UNAUTHORIZED => 'unauthorized',
            Response::HTTP_FORBIDDEN => 'forbidden',
            Response::HTTP_NOT_FOUND => 'not_found',
            Response::HTTP_TOO_MANY_REQUESTS => 'rate_limited',
            default => 'http_error',
        };
    }
}
