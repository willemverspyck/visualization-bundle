<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\RequestInterface;

final class LimitFilter extends AbstractOptionFilter
{
    public function __construct()
    {
        $this->setType(FilterInterface::TYPE_INPUT);
    }

    public function getField(): string
    {
        return RequestInterface::LIMIT;
    }

    public static function getName(): string
    {
        return RequestInterface::LIMIT;
    }
}
