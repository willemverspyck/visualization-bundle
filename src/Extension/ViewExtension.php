<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Extension;

use DateTimeInterface;
use Exception;
use Spyck\VisualizationBundle\Config\Config;
use Spyck\VisualizationBundle\Entity\Widget;
use Spyck\VisualizationBundle\Field\AbstractFieldInterface;
use Spyck\VisualizationBundle\Field\FieldInterface;
use Spyck\VisualizationBundle\Field\MultipleFieldInterface;
use Spyck\VisualizationBundle\Model\Block;
use Spyck\VisualizationBundle\Service\ChartService;
use Spyck\VisualizationBundle\Utility\ViewUtility;
use Spyck\VisualizationBundle\Utility\WidgetUtility;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

#[Autoconfigure(tags: ['twig.extension'])]
final class ViewExtension extends AbstractExtension
{
    public function __construct(private readonly ChartService $chartService, private readonly KernelInterface $kernel)
    {
    }

    /**
     * Set the functions for this extension.
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getChart', [$this, 'getChart']),
            new TwigFunction('getClasses', [$this, 'getClasses']),
            new TwigFunction('getDirectory', [$this, 'getDirectory']),
            new TwigFunction('getFields', [$this, 'getFields']),
            new TwigFunction('getNumber', [$this, 'getNumber']),
            new TwigFunction('getStyles', [$this, 'getStyles']),
            new TwigFunction('hasChart', [$this, 'hasChart']),
            new TwigFunction('hasMultipleFields', [$this, 'hasMultipleFields']),
            new TwigFunction('isMultipleField', [$this, 'isMultipleField']),
        ];
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
        return sprintf('%s%s', $this->kernel->getProjectDir(), $value);
    }

    public function getClasses(AbstractFieldInterface $field): array
    {
        $classesForGroup = ViewUtility::getClasses($field, true);
        $classes = ViewUtility::getClasses($field, false);

        return [$field->getType(), ...$classesForGroup, ...$classes];
    }

    public function getFields(array $fields): array
    {
        return WidgetUtility::mapFields($fields, function (FieldInterface $field): FieldInterface {
            return $field;
        });
    }

    public function getNumber(Config $config, float|int $value): string
    {
        return ViewUtility::getNumber($config, $value);
    }

    public function getStyles(AbstractFieldInterface $field, array|bool|DateTimeInterface|float|int|string|null $value): array
    {
        $stylesForGroup = ViewUtility::getStyles($field, $value, true);
        $styles = ViewUtility::getStyles($field, $value, false);

        return [...$stylesForGroup, ...$styles];
    }

    public function hasChart(Block $block): bool
    {
        if (false === $this->chartService->hasChart()) {
            return false;
        }

        $charts = $block->getCharts();

        if (0 === count($charts)) {
            return false;
        }

        return Widget::CHART_TABLE !== $charts[0];
    }

    public function hasMultipleFields(array $fields): bool
    {
        foreach ($fields as $field) {
            if ($field instanceof MultipleFieldInterface) {
                return true;
            }
        }

        return false;
    }

    public function isMultipleField(AbstractFieldInterface $field): bool
    {
        return $field instanceof MultipleFieldInterface;
    }
}
