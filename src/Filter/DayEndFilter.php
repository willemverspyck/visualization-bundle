<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\RequestInterface;

final class DayEndFilter extends AbstractFilter
{
    public static function getField(): string
    {
        return RequestInterface::DATE_END;
    }

    public static function getName(): string
    {
        return RequestInterface::DATE_END;
    }

    public function getType(): string
    {
        return FilterInterface::TYPE_DATE;
    }
}
