<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Normalizer;

use Spyck\VisualizationBundle\Parameter\EntityParameterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer as BaseAbstractNormalizer;

final class EntityParameterNormalizer extends AbstractNormalizer
{
    /**
     * @param EntityParameterInterface $data
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $group = $data->getGroup();

        if (null !== $group) {
            $context[BaseAbstractNormalizer::GROUPS][] = $group;
        }

        return $this->normalizer->normalize($data->getDataAsObject(), $format, $context);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof EntityParameterInterface;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            EntityParameterInterface::class => false,
        ];
    }
}
