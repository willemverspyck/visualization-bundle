<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Chart;

class TableChart implements ChartInterface
{
    public static function getCode(): string
    {
        return ChartInterface::TABLE;
    }
}
