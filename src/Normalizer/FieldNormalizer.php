<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Normalizer;

use Spyck\VisualizationBundle\Field\MultipleFieldInterface;

final class FieldNormalizer extends AbstractNormalizer
{
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $this->setNormalized($data);

        $normalize = $this->normalizer->normalize($data, $format, $context);
        $normalize['children'] = [];

        foreach ($data->getChildren() as $child) {
            if ($child->isActive()) {
                $normalize['children'][] = $this->normalizer->normalize($child, $format, $context);
            }
        }

        return $normalize;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if ($this->isNormalized($data)) {
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
