<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class DarkwoodApiExceptionSubscriber implements EventSubscriberInterface
{
    private const PATH_PREFIX = '/api/darkwood/';

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

        $event->setResponse(new JsonResponse([
            'error' => 'internal_error',
            'message' => 'An internal error occurred',
        ], Response::HTTP_INTERNAL_SERVER_ERROR));
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
