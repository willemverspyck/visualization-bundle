<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\AbstractMultipleRequest;

final class PaginationFilter extends AbstractMultipleRequest
{
    public function __construct()
    {
        $limitFilter = new LimitFilter();
        $limitFilter->setParent($this);

        $this->addChild($limitFilter);

        $offsetFilter = new OffsetFilter();
        $offsetFilter->setParent($this);

        $this->addChild($offsetFilter);
    }
}
