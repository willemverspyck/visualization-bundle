<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Normalizer;

use Spyck\VisualizationBundle\Entity\Download;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final class DownloadNormalizer extends AbstractNormalizer
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $this->setNormalized($data);

        $name = 'spyck_visualization_download_item';
        $parameters = [
            'downloadId' => $data->getId(),
        ];

        $normalize = $this->normalizer->normalize($data, $format, $context);
        $normalize['url'] = $this->router->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);

        return $normalize;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if ($this->isNormalized($data)) {
            return false;
        }

        return $data instanceof Download;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Download::class => false,
        ];
    }
}
