<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Normalizer;

use Spyck\VisualizationBundle\Entity\Bookmark;
use Symfony\Component\Routing\RouterInterface;

final class BookmarkNormalizer extends AbstractNormalizer
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $this->setNormalized($data, $context);

        $name = 'spyck_visualization_dashboard_show';
        $parameters = [
            'dashboardId' => $data->getDashboard()->getId(),
            ...$data->getVariables(),
        ];

        $normalize = $this->normalizer->normalize($data, $format, $context);
        $normalize['url'] = $this->router->generate($name, $parameters);

        return $normalize;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if ($this->hasNormalized($data, $context)) {
            return false;
        }

        return $data instanceof Bookmark;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Bookmark::class => false,
        ];
    }
}
