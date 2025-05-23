<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\RequestInterface;

final class LimitFilter extends AbstractFilter
{
    public static function getField(): string
    {
        return RequestInterface::LIMIT;
    }

    public static function getName(): string
    {
        return RequestInterface::LIMIT;
    }

    public function getType(): string
    {
        return FilterInterface::TYPE_NUMBER;
    }
}
