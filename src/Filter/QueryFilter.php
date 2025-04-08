<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\RequestInterface;

final class QueryFilter extends AbstractFilter
{
    public static function getField(): string
    {
        return RequestInterface::QUERY;
    }

    public static function getName(): string
    {
        return RequestInterface::QUERY;
    }

    public function getType(): string
    {
        return FilterInterface::TYPE_TEXT;
    }
}
