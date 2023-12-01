<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Parameter;

use Spyck\VisualizationBundle\Request\AbstractMultipleRequest;

final class MonthRangeParameter extends AbstractMultipleRequest
{
    public function __construct()
    {
        $this
            ->addChild(new MonthStartParameter())
            ->addChild(new MonthEndParameter());
    }
}
