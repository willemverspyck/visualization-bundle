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

    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $this->setNormalized($data, $context);

        $normalize = $this->normalizer->normalize($data, $format, $context);
        $normalize['dashboard'] = null;

        if (null === $data->getDashboard()) {
            return $normalize;
        }

        $route = $this->dashboardService->getRoute($data->getDashboard(), $data->getVariables());

        if (null === $route) {
            return $normalize;
        }

        $normalize['dashboard'] = $this->normalizer->normalize($route, $format, $context);

        return $normalize;
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
