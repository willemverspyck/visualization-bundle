<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const string KEY = 'spyck_visualization_normalizer';

    protected function isNormalize(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (false === array_key_exists(self::KEY, $context)) {
            return false;
        }

        return in_array($this->getKey($data), $context[self::KEY], true);
    }

    protected function getNormalize(mixed $data, ?string $format = null, array $context = []): array
    {
        if (false === array_key_exists(self::KEY, $context)) {
            $context[self::KEY] = [];
        }

        $context[self::KEY][] = $this->getKey($data);

        return $this->normalizer->normalize($data, $format, $context);
    }

    private function getKey(mixed $data): string
    {
        return spl_object_hash($data);
    }
}
