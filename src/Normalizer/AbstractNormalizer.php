<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const KEY = 'normalized';

    protected function setNormalized(mixed $data, array &$context): void
    {
        if (false === array_key_exists(self::KEY, $context)) {
            $context[self::KEY] = [];
        }

        $context[self::KEY][] = $this->getKey($data);
    }

    protected function hasNormalized(mixed $data, array $context): bool
    {
        if (false === is_object($data)) {
            return false;
        }

        if (false === array_key_exists(self::KEY, $context)) {
            $context[self::KEY] = [];
        }

        return in_array($this->getKey($data), $context[self::KEY]);
    }

    protected function getKey(object $object): string
    {
        return sprintf('%s#%s', spl_object_hash($this), spl_object_hash($object));
    }
}
