<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Chart;

final class BarChart implements ChartInterface
{
    public static function getCode(): string
    {
        return ChartInterface::BAR;
    }
}
