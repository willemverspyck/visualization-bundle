<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\AbstractMultipleRequest;

final class PaginationFilter extends AbstractMultipleRequest
{
    public function __construct()
    {
        $this
            ->addChild(new LimitFilter())
            ->addChild(new OffsetFilter());
    }
}
