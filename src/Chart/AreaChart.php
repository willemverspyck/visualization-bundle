<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Chart;

final class AreaChart implements ChartInterface
{
    public static function getCode(): string
    {
        return ChartInterface::AREA;
    }
}
