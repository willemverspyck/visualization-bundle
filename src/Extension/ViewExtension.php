<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Extension;

use Exception;
use Spyck\VisualizationBundle\Model\Block;
use Spyck\VisualizationBundle\Service\ChartService;
use Spyck\VisualizationBundle\Utility\NumberUtility;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

#[Autoconfigure(tags: ['twig.extension'])]
final class ViewExtension extends AbstractExtension
{
    public function __construct(private readonly ChartService $chartService, #[Autowire(param: 'spyck.visualization.directory')] private readonly string $directory)
    {
    }

    /**
     * Set the functions for this extension.
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getAbbreviation', [$this, 'getAbbreviation']),
            new TwigFunction('getChart', [$this, 'getChart']),
            new TwigFunction('getDirectory', [$this, 'getDirectory']),
        ];
    }

    /**
     * @throws Exception
     */
    public function getAbbreviation(float|int $value, int $precision = 0): string
    {
        return NumberUtility::getAbbreviation($value, $precision);
    }

    /**
     * @throws Exception
     */
    public function getChart(Block $block): string
    {
        return $this->chartService->getChart($block);
    }

    /**
     * @throws Exception
     */
    public function getDirectory(string $value): string
    {
        return sprintf('%s%s', $this->directory, $value);
    }
}
