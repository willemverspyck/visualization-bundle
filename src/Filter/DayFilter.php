<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\RequestInterface;

final class DayFilter extends AbstractFilter
{
    public static function getField(): string
    {
        return RequestInterface::DATE;
    }

    public static function getName(): string
    {
        return RequestInterface::DATE;
    }

    public function getType(): string
    {
        return FilterInterface::TYPE_DATE;
    }
}
