<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\RequestInterface;

final class DayFilter extends AbstractDateFilter
{
    public static function getField(): string
    {
        return RequestInterface::DATE;
    }

    public static function getName(): string
    {
        return RequestInterface::DATE;
    }
}
