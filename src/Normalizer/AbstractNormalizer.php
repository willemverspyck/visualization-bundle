<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    protected function isNormalize(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (false === array_key_exists(static::class, $context)) {
            return false;
        }

        return in_array($this->getKey($data), $context[static::class], true);
    }

    protected function getNormalize(mixed $data, ?string $format = null, array $context = []): array
    {
        if (false === array_key_exists(static::class, $context)) {
            $context[static::class] = [];
        }

        $context[static::class][] = $this->getKey($data);

        return $this->normalizer->normalize($data, $format, $context);
    }

    private function getKey(mixed $data): string
    {
        return spl_object_hash($data);
    }
}
