<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\RequestInterface;

final class OffsetFilter extends AbstractFilter
{
    public static function getField(): string
    {
        return RequestInterface::OFFSET;
    }

    public static function getName(): string
    {
        return RequestInterface::OFFSET;
    }

    public function getType(): string
    {
        return FilterInterface::TYPE_NUMBER;
    }
}
