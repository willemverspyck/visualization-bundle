<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\RequestInterface;

final class OffsetFilter extends AbstractOptionFilter
{
    public function __construct()
    {
        $this->setConfig([
            'type' => FilterInterface::TYPE_INPUT,
        ]);
    }

    public static function getField(): string
    {
        return RequestInterface::OFFSET;
    }

    public static function getName(): string
    {
        return RequestInterface::OFFSET;
    }
}
