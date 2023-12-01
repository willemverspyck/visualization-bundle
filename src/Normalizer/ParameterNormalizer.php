<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Normalizer;

use Spyck\VisualizationBundle\Parameter\EntityParameterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer as BaseAbstractNormalizer;

final class ParameterNormalizer extends AbstractNormalizer
{
    /**
     * {@inheritDoc}
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        $group = $object->getGroup();

        if (null !== $group) {
            $context[BaseAbstractNormalizer::GROUPS][] = $group;
        }

        return $this->normalizer->normalize($object->getDataAsObject(), $format, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof EntityParameterInterface;
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            EntityParameterInterface::class => false,
        ];
    }
}
