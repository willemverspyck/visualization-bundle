<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Normalizer;

use Spyck\VisualizationBundle\Entity\Menu;
use Spyck\VisualizationBundle\Service\DashboardService;

final class MenuNormalizer extends AbstractNormalizer
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $this->setNormalized($object, $context);

        $data = $this->normalizer->normalize($object, $format, $context);
        $data['dashboard'] = null;

        if (null === $object->getDashboard()) {
            return $data;
        }

        $route = $this->dashboardService->getDashboardRoute($object->getDashboard(), $object->getVariables());

        if (null === $route) {
            return $data;
        }

        $data['dashboard'] = $this->normalizer->normalize($route, $format, $context);

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if ($this->hasNormalized($data, $context)) {
            return false;
        }

        return $data instanceof Menu;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Menu::class => false,
        ];
    }
}
