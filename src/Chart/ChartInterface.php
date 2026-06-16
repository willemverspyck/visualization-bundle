<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Chart;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['spyck.visualization.chart'])]
interface ChartInterface
{
    public const string AREA = 'area';
    public const string BAR = 'bar';
    public const string COLUMN = 'column';
    public const string COUNTRY = 'country';
    public const string GANTT = 'gantt';
    public const string LINE = 'line';
    public const string PIE = 'pie';
    public const string REGION = 'region';
    public const string TABLE = 'table';

    public static function getCode(): string;
}
