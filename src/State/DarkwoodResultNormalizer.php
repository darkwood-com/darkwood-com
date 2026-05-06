<?php

declare(strict_types=1);

namespace App\State;

use BackedEnum;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\Response;
use Traversable;

use function is_array;
use function is_scalar;
use function json_decode;

final readonly class DarkwoodResultNormalizer
{
    public function normalize(mixed $value): mixed
    {
        if ($value instanceof Response) {
            $content = $value->getContent();
            if ($content === false || $content === '') {
                return null;
            }

            $decoded = json_decode($content, true);

            return $decoded ?? $content;
        }

        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalize($item);
            }

            return $normalized;
        }

        if ($value instanceof Traversable) {
            $normalized = [];
            foreach ($value as $item) {
                $normalized[] = $this->normalize($item);
            }

            return $normalized;
        }

        if (method_exists($value, 'getId')) {
            return $value->getId();
        }

        if (method_exists($value, '__toString')) {
            return (string) $value;
        }

        return null;
    }
}
