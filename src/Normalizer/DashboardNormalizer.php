<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Normalizer;

use Spyck\VisualizationBundle\Entity\Dashboard;
use Spyck\VisualizationBundle\Service\DashboardService;

final class DashboardNormalizer extends AbstractNormalizer
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $this->setNormalized($data);

        return $this->dashboardService->getRoute($data)->toArray();
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if ($this->isNormalized($data)) {
            return false;
        }

        return $data instanceof Dashboard;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Dashboard::class => false,
        ];
    }
}
