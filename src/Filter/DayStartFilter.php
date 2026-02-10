<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\RequestInterface;

final class DayStartFilter extends AbstractFilter
{
    public static function getField(): string
    {
        return RequestInterface::DATE_START;
    }

    public static function getName(): string
    {
        return RequestInterface::DATE_START;
    }

    public function getType(): string
    {
        return FilterInterface::TYPE_DATE;
    }
}
