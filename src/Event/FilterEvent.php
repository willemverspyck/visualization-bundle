<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Event;

use Spyck\VisualizationBundle\Filter\FilterInterface;
use Spyck\VisualizationBundle\Widget\WidgetInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class FilterEvent extends Event
{
    private array $options = [];

    public function __construct(private readonly FilterInterface $filter, private readonly WidgetInterface $widget)
    {
    }

    public function getFilter(): FilterInterface
    {
        return $this->filter;
    }

    public function getWidget(): WidgetInterface
    {
        return $this->widget;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
