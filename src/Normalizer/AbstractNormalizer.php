<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private array $normalized = [];

    protected function isNormalized(mixed $data): bool
    {
        if (false === is_object($data)) {
            return false;
        }

        return in_array($this->getKey($data), $this->normalized, true);
    }

    protected function setNormalized(mixed $data): void
    {
        $this->normalized[] = $this->getKey($data);
    }

    protected function getKey(object $object): string
    {
        return sprintf('%s_%s', spl_object_hash($this), spl_object_hash($object));
    }
}
