<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Normalizer;

use Spyck\VisualizationBundle\Field\MultipleFieldInterface;

final class FieldNormalizer extends AbstractNormalizer
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $this->setNormalized($object, $context);

        $data = $this->normalizer->normalize($object, $format, $context);
        $data['children'] = [];

        foreach ($object->getChildren() as $child) {
            if ($child->isActive()) {
                $data['children'][] = $this->normalizer->normalize($child, $format, $context);
            }
        }

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if ($this->hasNormalized($data, $context)) {
            return false;
        }

        return $data instanceof MultipleFieldInterface;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            MultipleFieldInterface::class => false,
        ];
    }
}
