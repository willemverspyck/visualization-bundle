<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Chart;

final class GanttChart implements ChartInterface
{
    public static function getCode(): string
    {
        return ChartInterface::GANTT;
    }
}
